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
        return true;
    }

    private function _updateEntry($entry)
    {
        return Warranty_model::where('warranty.serial_number', $entry['serial_number'])
                ->where('purchase_date', '!=', $entry['purchase_date'])
                ->where('end_date', '!=', $entry['end_date'])
                ->update([
                    'purchase_date' => $entry['purchase_date'],
                    'end_date' => $entry['end_date'],
                ]);
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
