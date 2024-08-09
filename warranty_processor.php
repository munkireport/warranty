<?php

use CFPropertyList\CFPropertyList;
use munkireport\processors\Processor;

class Warranty_processor extends Processor
{
    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author abn290
     **/
    public function run($plist)
    {
        // If plist is empty, echo out error
        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $mylist = $parser->toArray();

        $model = Warranty_model::firstOrNew(['serial_number' => $this->serial_number]);

        // Update the expired date if it current date is beyond the warranty end date
        if ($model->end_date && $model->end_date < date('Y-m-d')){
            $mylist["status"] = "Expired";
        }

        // Process coverage end date
        if (array_key_exists("coverage_end_date", $mylist)) {
            $mylist["end_date"] = date('Y-m-d', $mylist["coverage_end_date"]);
        }

        // Process estimated manufactured date
        // Get machine model
        $serial_number = $this->serial_number;
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

                // These arrarys should never change
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

            // Check if mfg date is more than 4 years ago
            $four_years_ago = date('Y-m-d', strtotime(' - 4 years'));
            if ( $mfg_date < $four_years_ago ){
                // If it is, expire the warranty
                $mylist["status"] = "Expired";
            }
        }

        // The warranty module uses purchase date as manufactured date
        $mylist["est_mfg_date"] = $mfg_date;

        // Generate purchase date from Limited Warranty if there is no purchase date or if purchase and mfg date are the same
        $warranty_original = $model->getOriginal();
        if (array_key_exists("purchase_date", $warranty_original) && array_key_exists("status", $mylist)){
            $purchase_date = $warranty_original["purchase_date"];
            if ((is_null($purchase_date) || $purchase_date == $mfg_date) && $mylist["status"] == "Limited Warranty" && array_key_exists("end_date", $mylist) && ! is_null($mylist["end_date"])){
                $new_purchase_date = new DateTime($mylist["end_date"].' - 1 year');
                $mylist["purchase_date"] = $new_purchase_date->format('Y-m-d');
            }
        }

        $model->fill($mylist);
        $model->save();
    }
}