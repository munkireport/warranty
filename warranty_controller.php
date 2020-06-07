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
        require $this->module_path . '/warranty_upload.php';
        $uploader = new Warranty_upload;
        view('admin_form', ['result' => $uploader->handleUpload()], $this->module_path . '/views/');
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
     *
     * @return void
     * @author
     **/
    public function estimate_manufactured_date($serial_number = '')
    {
        require_once(__DIR__ . "/helpers/warranty_helper.php");
        jsonView(['date' => estimate_manufactured_date($serial_number)]);
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
        }else{
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
     * Generate age data for age widget
     *
     * @author AvB
     **/
    public function age()
    {

        $ages = [];

        $now = date_create();
        foreach(Warranty_model::select('purchase_date')->filter()->get()->toArray() as $item){
            // Check if purchase date is valid
            if( ! $purchase_date = date_create($item['purchase_date'])){
                continue;
            }
            if($interval = date_diff($now, $purchase_date)){
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
