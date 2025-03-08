<?php

/**
 * Warranty module class
 *
 * @package munkireport
 * @author AvB
 **/
class Warranty_controller extends Module_controller
{
    public function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }

    public function admin()
    {
        // Check if the user is authorized and has admin role
        if (! $this->authorized()) {
            die('Authenticate first.');
        }

        if (! $this->authorized('global')) {
            die('You need to be admin');
        }

        require $this->module_path . '/warranty_upload.php';
        $uploader = new Warranty_upload;
        $obj = new View();
        $obj->view('admin_form', ['result' => $uploader->handleUpload()], $this->module_path . '/views/');
    }

    public function update_status()
    {
        jsonView(
            [
                'updated' => Warranty_model::where('end_date', '<', date('Y-m-d'))
                    ->where('warranty.status', '!=', 'Expired')
                    ->update(['warranty.status' => 'Expired'])
            ]
        );
    }

    public function report($serial_number = '')
    {
        jsonView(
            Warranty_model::where('warranty.serial_number', $serial_number)
                ->filter()
                ->first()
                ->toArray()
        );
    }

    /**
     * Get estimate_manufactured_date
     * See http://www.macrumors.com/2010/04/16/apple-tweaks-serial-number-format-with-new-macbook-pro/ for details about serial numbers
     * 
     * This function does not work with Apple's new random serial numbers starting with the 2021 14"/16" MacBook Pro
     *
     * @return void
     * @author AvB
     **/
    public function estimate_manufactured_date($serial_number = '')
    {
        // Remove non-serial number characters
        $serial_number = preg_replace("/[^A-Za-z0-9_\-]]/", '', $serial_number);

        // Get machine model
        $machine_model = Machine_model::select('machine.machine_model')->where('machine.serial_number', $serial_number)
                            ->first()
                            ->toArray()["machine_model"];

        $mfg_date = 'Unknown';

        // We only want to run this function on pre-random serial number Macs
        if ((strpos($machine_model, "iMac") !== false || strpos($machine_model, "MacBook") !== false || strpos($machine_model, "Macmini") !== false || strpos($machine_model, "MacPro") !== false || strpos($machine_model, "Xserve") !== false) && ! strpos($machine_model, "MacBookPro18") !== false){

            if (strlen($serial_number) == 11) {
                $year = $serial_number[2];
                $est_year = 2000 + strpos('   3456789012', $year);
                $week = max(1, intval(substr($serial_number, 3, 2)));
                $strtime = sprintf('%sW%02s1', $est_year, $week);
                $mfg_date = date('Y-m-d', strtotime($strtime));
            } else if (strlen($serial_number) == 12) {

                // These arrary should never change
                $macs_2020 = array("MacBookAir9,1", "MacBookAir10,1", "MacBookPro16,3", "MacBookPro16,2", "MacBookPro17,1", "MacBookPro18,1", "MacBookPro18,2", "MacBookPro18,3", "MacBookPro18,4", "iMac20,1", "iMac20,2", "iMac21,1", "iMac21,2", "Macmini9,1");
                $macs_2010_2020 = array("MacBookAir8,2", "MacBookPro16,1", "MacBookPro16,4", "MacBookPro15,4", "MacBookPro15,2", "MacBookPro18,2", "MacBookPro18,3", "iMacPro1,1", "iMac18,1", "iMac19,1", "iMac19,2", "Macmini8,1", "MacPro7,1");

                $year_code = 'cdfghjklmnpqrstvwxyz';
                $year = strtolower($serial_number[3]);
                $est_year = intval(strpos($year_code, $year) / 2);

                // Set Mac decade
                if (in_array($machine_model, $macs_2020)){
                    $decade = 2020;
                } else if (in_array($machine_model, $macs_2010_2020)){
                    // For Macs made in 2010's and 2020's, check the year date for decade comparison
                    if ($est_year < 6){
                        $decade = 2020;
                    } else {
                        $decade = 2010;
                    }
                } else {
                    $decade = 2010;
                }

                $est_year = $decade + $est_year;
                $est_half = strpos($year_code, $year) % 2;
                $week_code = ' 123456789cdfghjklmnpqrtvwxy';
                $week = strtolower(substr($serial_number, 4, 1));
                $est_week = strpos($week_code, $week) + ($est_half * 26);            
                $strtime = sprintf('%sW%02s1', $est_year, $est_week);
                $mfg_date = date('Y-m-d', strtotime($strtime));
            }
        }

        jsonView(['date' => $mfg_date]);
    }
    
    /**
     * Get Warranty statistics
     *
     * @param bool $alert Filter on 30 days
     **/
    public function get_stats($alert = false)
    {
        if ($alert) {
            $between = [date('Y-m-d'), date('Y-m-d', strtotime('+30days'))];
        } else {
            $between = [date('Y-m-d', strtotime('-20years')), date('Y-m-d', strtotime('+20 years'))];
        }

        jsonView(
            Warranty_model::selectRaw('count(*) as count, warranty.status')
                ->whereBetween('end_date', $between)
                ->filter()
                ->groupBy('warranty.status')
                ->orderBy('count', 'desc')
                ->get()
        );
    }

    /**
     * Get Warranty statistics
     *
     * @param bool $alert Filter on 30 days
     **/
    public function get_machines_expiring_next_month()
    {
        $between = [date('Y-m-d'), date('Y-m-d', strtotime('+30days'))];

        jsonView(
            Warranty_model::selectRaw('machine.computer_name, warranty.status')
                ->whereBetween('end_date', $between)
                ->join('machine', 'machine.serial_number', '=', 'warranty.serial_number')    
                ->filter()
                ->orderBy('end_date', 'desc')
                ->get()
                ->toArray()
        );
    }

    /**
     * Generate age data for age widget, using est_mfg_date and purchase date
     *
     * @author AvB
     **/
    public function age()
    {

        $ages = [];
        $out = [];

        $now = date_create();
        foreach(Warranty_model::select('est_mfg_date','purchase_date')->filter()->get()->toArray() as $item){

            // Check that est mfg date is null or unknown and that we have a purchase date
            if((is_null($item['est_mfg_date']) || $item['est_mfg_date'] == "Unknown") && !is_null($item['purchase_date'])){
                // Use purchase date as mfg date
                $item['est_mfg_date'] = $item['purchase_date'];
            }

            // Check if est mfg date is valid
            if(!is_null($item['est_mfg_date']) && ! $est_mfg_date = date_create($item['est_mfg_date'])){
                continue;
            }

            if($interval = date_diff($now, $est_mfg_date)){
                $age = (int) $interval->format('%y');
                $ages[$age] = $ages[$age] ?? 0;
                $ages[$age]++;
            }
        }

        ksort($ages);

        foreach($ages as $label => $value){
            if ($label == 0){
                $label = '<1';
            }
            $out[] = ['label' => $label, 'count' => $value];
        }

        jsonView($out);
    }
} // END class Warranty_module
