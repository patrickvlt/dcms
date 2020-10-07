<?php
namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Illuminate\Support\Facades\Schema;
use League\Flysystem\FileExistsException;
use Pveltrop\DCMS\Classes\Datatable;
use Pveltrop\DCMS\Classes\PHPExcel;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait DCMSController
{
    public $prefix;
    public $class;
    public $file;
    public $requestFile;
    public $modelRequest;
    public $indexQuery;
    public $indexView;
    public $showView;
    public $editView;
    public $createView;
    public $createdUrl;
    public $createdTitle;
    public $createdMessage;
    public $updatedUrl;
    public $updatedTitle;
    public $updatedMessage;
    public $deletedUrl;
    public $deletedTitle;
    public $deletedMessage;
    public $importCols;
    public $importFailedTitle;
    public $importFailedMessage;
    public $importEmptyTitle;
    public $importEmptyMessage;
    public $importFinishedTitle;
    public $importFinishedMessage;
    public $importedUrl;
    public $autoFixColumns;

    // This returns void by default
    public function DCMS(): void {}

    public function __construct()
    {
        if (!app()->runningInConsole()) {
            // Route prefix
            $this->prefix = $this->DCMS()['routePrefix'] ?? GetPrefix();
            // Get model and custom request class
            if (!isset($this->DCMS()['model'])){
                throw new \RuntimeException("No model defined for: ".ucfirst($this->prefix)." in DCMS function.");
            } else {
                $this->model = $this->DCMS()['model'];
            }
            if (!isset($this->DCMS()['request'])){
                throw new \RuntimeException("No custom request defined for: ".ucfirst($this->prefix)." in DCMS function.");
            } else {
                $this->modelRequest = $this->DCMS()['request'];
                $this->modelRequest = (new $this->modelRequest);
            }
            // CRUD views
            $this->indexView = $this->DCMS()['views']['index'] ?? 'index';
            $this->showView = $this->DCMS()['views']['show'] ?? 'show';
            $this->editView = $this->DCMS()['views']['edit'] ?? 'edit';
            $this->createView = $this->DCMS()['views']['create'] ?? 'create';
            // JSON CRUD responses
            $this->createdUrl = $this->DCMS()['created']['url'] ?? '/'.$this->prefix;
            $this->createdTitle = $this->DCMS()['created']['title'] ?? __(ucfirst($this->prefix)).__(' ').__('created');
            $this->createdMessage = $this->DCMS()['created']['message'] ?? __(ucfirst($this->prefix)).__(' ').__('has been successfully created');
            $this->updatedUrl = $this->DCMS()['updated']['url'] ?? '/'.$this->prefix;
            $this->updatedTitle = $this->DCMS()['updated']['title'] ?? __(ucfirst($this->prefix)).__(' ').__('updated');
            $this->updatedMessage = $this->DCMS()['updated']['message'] ?? __(ucfirst($this->prefix)).__(' ').__('has been successfully updated');
            $this->deletedUrl = $this->DCMS()['deleted']['url'] ?? '/'.$this->prefix;
            $this->deletedTitle = $this->DCMS()['deleted']['title'] ?? __(ucfirst($this->prefix)).__(' ').__('deleted');
            $this->deletedMessage = $this->DCMS()['deleted']['message'] ?? __(ucfirst($this->prefix)).__(' ').__('has been successfully deleted');
            // jExcel imports
            $this->importCols = $this->DCMS()['import']['columns'] ?? null;
            $this->importFailedTitle = $this->DCMS()['import']['failed']['title'] ?? __('Import failed');
            $this->importFailedMessage = $this->DCMS()['import']['failed']['message'] ?? __('Some fields contain invalid data.');
            $this->importEmptyTitle = $this->DCMS()['import']['empty']['title'] ?? __('Import failed');
            $this->importEmptyMessage = $this->DCMS()['import']['empty']['message'] ?? __('Please fill in data to import.');
            $this->importFinishedTitle = $this->DCMS()['import']['finished']['title'] ?? __('Import finished');
            $this->importFinishedMessage = $this->DCMS()['import']['finished']['message'] ?? __('All data has been succesfully imported.');
            $this->importedUrl = $this->DCMS()['imported']['url'] ?? '/'.$this->prefix;
            // jExcel autocorrect columns
            $this->autoFixColumns = $this->DCMS()['import']['autocorrect'] ?? null;
        }
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->indexQuery;
        }
        $vars = method_exists($this,'beforeIndex') ? $this->beforeIndex() : null;
        return view($this->prefix.'.'.$this->indexView)->with($vars);
    }

    public function fetch()
    {
        return (new Datatable((new $this->model)->query()))->render();
    }

    public function show($id)
    {
        ${$this->prefix} = (new $this->model)->FindOrFail($id);

        $vars = method_exists($this,'beforeShow') ? $this->beforeShow($id) : null;
        return view($this->prefix.'.'.$this->showView,compact(${$this->prefix}))->with($vars);
    }

    public function edit($id)
    {
        ${$this->prefix} = (new $this->model)->FindOrFail($id);

        $vars = method_exists($this,'beforeEdit') ? $this->beforeEdit($id) : null;
        return view($this->prefix.'.'.$this->editView,compact(${$this->prefix}))->with($vars);
    }

    public function create()
    {
        $vars = method_exists($this,'beforeCreate') ? $this->beforeCreate() : null;
        return view($this->prefix.'.'.$this->createView)->with($vars);
    }

    public function crud($createdOrUpdated,$id=null)
    {
        $requestData = request()->all();
        // Merge with modified request from beforeValidation()
        $uploadRules = method_exists($this->modelRequest,'uploadRules') ? $this->modelRequest->uploadRules() : false;
        $requestRules = method_exists($this->modelRequest,'rules') ? $this->modelRequest->rules() : false;
        $requestMessages = method_exists($this->modelRequest,'messages') ? $this->modelRequest->messages() : false;
        $beforeValidation = method_exists($this->modelRequest,'beforeValidation') ? $this->modelRequest->beforeValidation() : false;
        if ($beforeValidation){
            foreach ($beforeValidation as $changingKey => $changingValue){
                $requestData[$changingKey] = $changingValue;
            }
        }
        $filesToRemove = [];
        if ($uploadRules){
            foreach ($uploadRules as $uploadKey => $uploadRule){
                $key = explode('.',$uploadKey);
                $key = $key[0];
                if (array_key_exists($key, $requestData)){
                    foreach ($requestData[$key] as $x => $file){
                        // Check if file uploads has this applications URL in it
                        // If it doesnt have the url in its filename, then it has been tampered with
                        if (!strpos($file, env('APP_URL')) === 0){
                            return response()->json([
                                'message' => __('Invalid file'),
                                'errors' => [
                                    'file' => [
                                        $requestMessages[$uploadKey.'.noRemote'] ?? __('Remote files can\'t be added. Please upload a file on this page.')
                                    ]
                                ],
                            ], 422);
                        }
                        $checkFile = str_replace(env('APP_URL'),'',$file);
                        $checkFile = str_replace('/storage/','/public/',$checkFile);
                        $storedFile = Storage::exists($checkFile);
                        if ($storedFile){
                            $newFilePath = str_replace('/tmp/','/',$checkFile);
                            $filesToMove[] = [
                                'oldPath' => $checkFile,
                                'newPath' => $newFilePath
                            ];
                            $requestData[$key][$x] = str_replace('/public/','/storage/',$newFilePath);
                        } else {
                            return response()->json([
                                'message' => __('Invalid file'),
                                'errors' => [
                                    $key => [
                                        $requestMessages[$uploadKey.'.notFound'] ?? __('File couldn\'t be found. Try to upload it again to use it for ').$key.'.'
                                    ]
                                ],
                            ], 422);
                        }
                    }
                }
            }
        }
        $request = Validator::make($requestData, $requestRules, $requestMessages);
        $request = $request->validated();
        $afterValidation = method_exists($this->modelRequest,'afterValidation') ? $this->modelRequest->afterValidation($request) : false;
        // Merge with modified request from afterValidation()
        if ($afterValidation){
            foreach ($afterValidation as $modKey => $modValue){
                $request[$modKey] = $modValue;
            }
        }
        if ($createdOrUpdated === 'created'){
            ${$this->prefix} = (new $this->model)->create($request);
        } else if ($createdOrUpdated === 'updated') {
            ${$this->prefix} = (new $this->model)->findOrFail($id);
            // This is for files
            foreach ($request as $requestKey => $requestVal){
                // If request has an array, and points to storage, merge it with existing array if it has values already
                if (is_array($requestVal) && (strpos(implode(" ", $requestVal), '/storage/') !== false)) {
                    $newArr = [];
                    foreach ($requestVal as $val){
                        $newArr[] = $val;
                    }
                    try {
                        // check if object has an array for this already
                        $existing = ${$this->prefix}->$requestKey;
                        if(count($existing) > 0){
                            $newArr = array_merge($existing,$newArr);
                        }
                    } catch (\Throwable $th) {
                        //
                    }
                    // Check if array limit isnt being overridden
                    $loopRules = $requestRules[$requestKey];
                    $loopRules = (is_string($loopRules)) ? explode('|',$loopRules) : $loopRules;
                    foreach ($loopRules as $rule => $ruleVal){
                        $min = null;
                        $max = null;
                        if (strpos($ruleVal, 'min') === 0){
                            $min = explode(':',$ruleVal)[1];
                        }
                        if (strpos($ruleVal, 'max') === 0){
                            $max = explode(':',$ruleVal)[1];
                        }
                    }
                    if (count($newArr) > $max){
                        return response()->json([
                            'message' => __('File limit reached'),
                            'errors' => [
                                $requestKey => [
                                    $requestMessages[$requestKey.'.maxLimit'] ?? $requestKey.__(' can\'t have more than ').$max.__(' files.')
                                ]
                            ],
                        ], 422);
                    }
                    if (count($newArr) < $min){
                        return response()->json([
                            'message' => __('Missing files'),
                            'errors' => [
                                $requestKey => [
                                    $requestMessages[$requestKey.'.minLimit'] ?? $requestKey.__(' requires more than ').$min.__(' files.')
                                ]
                            ],
                        ], 422);
                    }
                    $request[$requestKey] = $newArr;
                }
            }
            ${$this->prefix}->update($request);
        }
        if (isset($filesToMove) && count($filesToMove) > 0){
            foreach ($filesToMove as $key => $file) {
                Storage::copy($file['oldPath'],$file['newPath']);
                Storage::delete($file['oldPath']);
            }
        }
        return $this->DCMSJSON(${$this->prefix},$createdOrUpdated);
    }

    public function store()
    {
        return $this->crud('created');
    }

    public function update($id)
    {
        return $this->crud('updated',$id);
    }

    public function destroy($id)
    {
        (new $this->model)->findOrFail($id)->delete();
    }

    public function DCMSJSON($object,$createdOrUpdated)
    {
        if ((isset($this->createdUrl) && $createdOrUpdated == 'created') || (isset($this->updatedUrl) && $createdOrUpdated == 'updated')){
            if (request()->ajax()){
                $redirect = $this->{$createdOrUpdated.'Url'};
            } else {
                return redirect($this->{$createdOrUpdated.'Url'});
            }
        } else {
            if (request()->ajax()){
                $redirect = '/'.$this->prefix;
            } else {
                $redirect = redirect()->route($this->prefix.'.index');
            }
        }
        // Title
        $title = $this->{$createdOrUpdated.'Title'};
        preg_match_all('/__\S*__/m',$title,$matches);
        foreach($matches[0] as $match){
            $prop = str_replace('__','',$match);
            $title = str_replace($match,$object->$prop,$title);
        }
        // Message
        $message = $this->{$createdOrUpdated.'Message'};
        preg_match_all('/__\S*__/m',$message,$matches);
        foreach($matches[0] as $match){
            $prop = str_replace('__','',$match);
            $message = str_replace($match,$object->$prop,$message);
        }
        return response()->json([
            'title' => $title,
            'message' => $message,
            'url' => $redirect
        ], 200);
    }

    public function StoreExport($data=null,$headers=null)
    {
        $data = $data ?? (new $this->model)->all()->toArray();

        if (!$headers){
            $autoHeaders = Schema::getColumnListing((new $this->model)->getTable());
            foreach ($autoHeaders as $headerKey => $headerVal){
                $headers[$headerVal] = $headerVal;
            }
        }

        $exportData = [];
        // Flatten the array to group nested results
        foreach (Flatten($data) as $key => $val){
            // Explode if key has a .
            $explodeKey = explode('.',$key);
            // Column to make logical results
            $column = end($explodeKey);
            // Row counter
            $row = $explodeKey[0];
            // This key should exist
            $matchKey = str_replace($row.'.','',$key);
            if (array_key_exists($matchKey, $headers)){
                // Push to export data if key matches with exportable headers
                $exportData[$row][$column] = $val;
            }
        }
        $fileName = RandomString().'.xlsx';
        PHPExcel::store($headers,$exportData,$fileName);
        return config('filesystems.disks')['tmp']['url'].'/'.$fileName;
    }

    public function ImportSheet()
    {
        $importData = request()->sheetData;
        //prepare sheet validation variables
        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');
        $x = 1;
        $errors = [];
        $failed = false;
        $nullableColumns = [];

        foreach ((new $this->modelRequest())->rules() as $key => $rule){
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
                    $this->validate($customRequest, (new $this->modelRequest())->rules(), (new $this->modelRequest())->messages());
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
