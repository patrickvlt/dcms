<?php

namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Pveltrop\DCMS\Classes\PHPExcel;

trait DCMSImports
{
    public function StoreExport($data, $headers=null)
    {
        $this->__init();
        if (!isset(config('filesystems.disks')['tmp'])) {
            throw new \RuntimeException("Please define a tmp filesystem in your config.");
        }
        $fileName = RandomString().'.xlsx';
        PHPExcel::store($headers, $data, $fileName);
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

        foreach ($this->request->rules() as $key => $rule) {
            $rule = is_array($rule) ? implode('|', $rule) : $rule;
            if (preg_match('/nullable/', $rule) || !preg_match('/required/', $rule)) {
                $nullableColumns[] = $key;
            }
        }

        if (!empty($importData)) {
            foreach ($importData as $row) {
                foreach ($row as $y => $col) {
                    // check if required columns arent empty
                    if ($col == null || ($col == '' && !in_array($col, $nullableColumns))) {
                        $failed = true;
                    }
                }

                // if data is ready for validation, add to the request
                if ($failed == false) {
                    foreach ($this->importCols as $x => $col) {
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
                foreach ($this->importCols as $x => $col) {
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

        // Get data from controller, class and attributes to use for autocorrection
        if ($this->autoFixColumns == null) {
            return false;
        }

        // search for column in jExcel constructor
        // this has to be done by finding the key/position of the column
        function searchForColumn($column, $array)
        {
            foreach ($array as $key => $val) {
                if ($val['column'] == $column) {
                    return $key;
                }
            }
            return null;
        }
        
        // Loop through table dropdown columns
        foreach (request()->th as $y => $header) {
            $jExcelColumn = searchForColumn($header['column'], $this->autoFixColumns);
            $jExcelColumn = isset($this->autoFixColumns[$jExcelColumn]) ? $this->autoFixColumns[$jExcelColumn] : null;
            if ($jExcelColumn) {
                //
                try {
                    $class = $jExcelColumn['class'];
                    $class = new $class;
                } catch (\Throwable $th) {
                    continue;
                }
                // Loop through data the user has sent
                foreach ($data as $x => $row) {
                    // Make a query for each Table Header
                    $query = $class::query();
                    // Strip whitespace from value and loop through the class` table to find a match
                    // Search by making dynamic where clauses
                    $value = $data[$x][$header['column']];
                    $value = str_replace(" ", "", $value);
                    foreach ($jExcelColumn['searchAttributes'] as $field) {
                        $query->orWhere($field, 'LIKE', '%'.$value.'%');
                    }
                    $match = $query->first();
                    // If a match is found, replace the cells value by the right attribute or id from the match
                    $returnAttr = isset($jExcelColumn['returnAttribute']) ? $jExcelColumn['returnAttribute'] : 'id';
                    $data[$x][$header['column']] = !empty($match) ? $match[$returnAttr] : $data[$x][$header['column']];
                }
            }
        }

        return $data;
    }
}
