<?php

namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Pveltrop\DCMS\Classes\PHPExcel;

trait DCMSImports {
    public function StoreExport($data,$headers=null)
    {      
        $this->__init();
        if (!isset(config('filesystems.disks')['tmp'])){
            throw new \RuntimeException("Please define a tmp filesystem in your config.");
        }
        $fileName = RandomString().'.xlsx';
        PHPExcel::store($headers,$data,$fileName);
        return config('filesystems.disks')['tmp']['url'].'/'.$fileName;
    }

    public function ImportSheet()
    {
        $this->__init();
        $importData = request()->sheetData;
        //prepare sheet validation variables
        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');
        $x = 1;
        $errors = [];
        $failed = false;
        $nullableColumns = [];

        foreach ($this->request->rules() as $key => $rule){
            if (preg_match('/nullable/',$rule) || !preg_match('/required/',$rule)){
                $nullableColumns[] = $key;
            }
        }

        if (!empty($importData)) {
            foreach ($importData as $row) {
                foreach ($row as $y => $col){
                    // check if required columns arent empty
                    if ($col == null || ($col == '' && !in_array($col, $nullableColumns))){
                        $failed = true;
                    }
                }

                // if data is ready for validation, add to the request
                if ($failed == false) {
                    foreach ($this->importCols as $x => $col){
                        $validateData[$x] = $row[$col];
                    }
                    $customRequest->request->add($validateData);
                    $this->validate($customRequest, $this->request->rules(), $this->request->messages());
                }
                $x++;
            }
            // if failed, return a JSON response
            if ($failed == true) {
                return response()->json(['response' => [
                    'title' => $this->importFailedTitle,
                    'message' => $this->importFailedMessage,
                ], 'errors' => $errors], 422);
            }
            //if succeeded, create objects and return a JSON response
            foreach ($importData as $row) {
                $passedData = [];
                // create new objects with data from jExcel table, as this has passed validation
                foreach ($this->importCols as $x => $col){
                    $passedData[$x] = $row[$col];
                }
                (new $this->model)->create($passedData);
            }
        } else {
            return response()->json(['response' => [
                'title' => $this->importFailedTitle,
                'message' => $this->importFailedMessage,
            ]], 422);
        }

        return response()->json(['response' => [
            'title' => $this->importFinishedTitle,
            'message' => $this->importFinishedMessage,
        ], 'url' => $this->importedUrl], 200);
    }

    public function FixSheet()
    {
        $this->__init();
        // Get data from ajax request at jexcel table
        $data = request()->data;
        $th = request()->th;

        // Get data from controller, class and columns to use for autocorrection
        if ($this->autoFixColumns == null){
            return false;
        }

        // Search for a column and retrieve its value
        function searchForColumn($column, $array) {
            foreach ($array as $key => $val) {
                if ($val['column'] == $column) {
                    return $key;
                }
            }
            return null;
        }
        // Loop through table dropdown columns
        foreach ($th as $y => $header){
            $column = searchForColumn($header['column'],$this->autoFixColumns);
            // Find the class which belongs to the provided prefix, sent from the table header in jExcel
            $class = FindClass(strtolower($column))['class'];
            $class = new $class;
            // Loop through data the user has sent
            foreach ($data as $x => $row){
                // Make a query for each Table Header
                $query = $class::query();
                $fields = $this->autoFixColumns[$column]['fields'];
                // Strip whitespace from value and loop through the class` table to find a match
                $value = $data[$x][$header['column']];
                $value = str_replace(" ","",$value);
                foreach ($fields as $field) {
                    $query->orWhere($field, 'LIKE', '%'.$value.'%');
                }
                $match = $query->get()->first();
                // If a match is found, replace the cells value by the id from the match
                $data[$x][$header['column']] = !empty($match) ? $match['id'] : $data[$x][$header['column']];
            }
        }


        return $data;
    }
}