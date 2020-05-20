<?php

class Warranty_upload
{
    public function __construct()
    {
        # code...
    }

    public function handleUpload()
    {
        if( ! isset( $_FILES['csv']['tmp_name'])){
            return;
        }

        $csv = $this->_convertFileToCsv($_FILES['csv']['tmp_name']);

        $result = [
            'csv_entries' => count($csv),
            'updated' => 0,
            'invalid' => 0,
        ];

        foreach ($csv as $entry) {
            if( ! $this->_validateEntry($entry)){
                $result['invalid'] += 1;
            }
            else{
                $result['updated'] += $this->_updateEntry($entry);
            }
        }

        return $result;
    }

    private function _validateEntry($entry)
    {
        if( ! isset($entry['serial_number'])){
            return false;
        }
        if( ! isset($entry['purchase_date'])){
            return false;
        }
        if( ! isset($entry['end_date'])){
            return false;
        }
        if( ! Reportdata_model::where('serial_number', $entry['serial_number'])->first()){
            return false;
        }

        return true;
    }

    private function _updateEntry($entry)
    {
        $warranty = Warranty_model::firstOrNew(['serial_number' => $entry['serial_number']]);
        $warranty->purchase_date = $entry['purchase_date'];
        $warranty->end_date = $entry['end_date'];
        $warranty->status = $entry['end_date'] >= date('Y-m-d') ? 'Supported' : 'Expired';
        if($warranty->isDirty()){
            $warranty->save();
            return true;
        }
        return false;
    }

    private function _convertFileToCsv($file)
    {
        $csv = array_map('str_getcsv', file($file));
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header
        return $csv;
    }
}
