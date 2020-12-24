<?php
namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Pveltrop\DCMS\Classes\Form;
use Pveltrop\DCMS\Classes\PHPExcel;
use Pveltrop\DCMS\Classes\Datatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait DCMSController
{
    public function __init()
    {
        if (!app()->runningInConsole()) {
            // Route prefix
            if (!isset($this->routePrefix)){
                throw new \RuntimeException("No routePrefix defined. Define this property in your controller constructor.");
            }
            // Get model and custom request class
            if (!isset($this->model)){
                throw new \RuntimeException("No model defined for: ".ucfirst($this->routePrefix)." in controller constructor.");
            }
            if (!isset($this->request)){
                throw new \RuntimeException("No custom request defined for: ".ucfirst($this->routePrefix)." in controller constructor.");
            } else {
                $this->request = (new $this->request());
            }

            // CRUD views
            $this->indexView = $this->views['index'] ?? 'index';
            $this->showView = $this->views['show'] ?? 'show';
            $this->editView = $this->views['edit'] ?? 'edit';
            $this->createView = $this->views['create'] ?? 'create';

            // JSON CRUD responses
            $this->createdUrl = $this->responses['created']['url'] ?? '/'.$this->routePrefix;
            $this->createdTitle = $this->responses['created']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('created');
            $this->createdMessage = $this->responses['created']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully created');
            $this->updatedUrl = $this->responses['updated']['url'] ?? '/'.$this->routePrefix;
            $this->updatedTitle = $this->responses['updated']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('updated');
            $this->updatedMessage = $this->responses['updated']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully updated');
            $this->deletedUrl = $this->responses['deleted']['url'] ?? '/'.$this->routePrefix;
            $this->deletedTitle = $this->responses['deleted']['title'] ?? __(ucfirst($this->routePrefix)).__(' ').__('deleted');
            $this->deletedMessage = $this->responses['deleted']['message'] ?? __(ucfirst($this->routePrefix)).__(' ').__('has been successfully deleted');
            $this->confirmDeleteUrl = $this->responses['confirmDelete']['url'] ?? '/'.$this->routePrefix;
            $this->confirmDeleteTitle = $this->responses['confirmDelete']['title'] ?? __('Confirm deletion');
            $this->confirmDeleteMessage = $this->responses['confirmDelete']['message'] ?? __('Do you want to delete this object?');
            $this->failedDeleteUrl = $this->responses['failedDelete']['url'] ?? '/'.$this->routePrefix;
            $this->failedDeleteTitle = $this->responses['failedDelete']['title'] ?? __('Deletion failed');
            $this->failedDeleteMessage = $this->responses['failedDelete']['message'] ?? __('Failed to delete this object. An unknown error has occurred.');

            // jExcel imports
            $this->importFailedTitle = $this->jExcel['responses']['failed']['title'] ?? __('Import failed');
            $this->importFailedMessage = $this->jExcel['responses']['failed']['message'] ?? __('Some fields contain invalid data.');
            $this->importEmptyTitle = $this->jExcel['responses']['empty']['title'] ?? __('Import failed');
            $this->importEmptyMessage = $this->jExcel['responses']['empty']['message'] ?? __('Please fill in data to import.');
            $this->importFinishedTitle = $this->jExcel['responses']['finished']['title'] ?? __('Import finished');
            $this->importFinishedMessage = $this->jExcel['responses']['finished']['message'] ?? __('All data has been succesfully imported.');
            $this->importedUrl = $this->jExcel['responses']['imported']['url'] ?? '/'.$this->routePrefix;
            $this->importCols = $this->jExcel['columns'] ?? null;
            // jExcel autocorrect columns
            $this->autoFixColumns = $this->jExcel['autocorrect'] ?? null;
        }
    }

    public function index()
    {
        $this->__init();
        $vars = method_exists($this,'beforeIndex') ? $this->beforeIndex() : null;
        return view($this->routePrefix.'.'.$this->indexView)->with($vars);
    }

    public function fetch()
    {
        $this->__init();
        return (new Datatable((new $this->model)->query()))->render();
    }

    public function show($id)
    {
        $this->__init();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->{$this->routePrefix});

        $vars = method_exists($this,'beforeShow') ? $this->beforeShow($id) : null;
        return view($this->routePrefix.'.'.$this->showView,compact(${$this->routePrefix}))->with($vars);
    }

    public function edit($id)
    {
        $this->__init();
        ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->{$this->routePrefix});
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
        $vars = method_exists($this,'beforeEdit') ? $this->beforeEdit($id) : null;
        return view($this->routePrefix.'.'.$this->editView,compact(${$this->routePrefix}))->with($vars)->with(['form' => $form]);
    }

    public function create()
    {
        $this->__init();
        $vars = method_exists($this,'beforeCreate') ? $this->beforeCreate() : null;
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request,$this->routePrefix,$this->form,$this->responses) : null;
        return view($this->routePrefix.'.'.$this->createView)->with($vars)->with(['form' => $form]);
    }

    public function crud($createdOrUpdated,$id=null)
    {
        $this->__init();
        $requestData = request()->all();
        // Merge with modified request from beforeValidation()
        $requestRules = method_exists($this->request,'rules') ? $this->request->rules() : false;
        $uploadRules = false;
        if($requestRules){
            $uploadRules = [];
            foreach ($requestRules as $key => $ruleArr) {
                $ruleArr = (is_string($ruleArr)) ? explode('|',$ruleArr) : $ruleArr;
                foreach ($ruleArr as $x => $rule) {
                    if (preg_match('/mimes/',$rule)){
                        $uploadRules[$key] = $ruleArr;
                        continue;
                    }
                }
            }
        }
        $requestMessages = method_exists($this->request,'messages') ? $this->request->messages() : false;
        $beforeValidation = method_exists($this->request,'beforeValidation') ? $this->request->beforeValidation($requestData) : false;
        if ($beforeValidation){
            foreach ($beforeValidation as $changingKey => $changingValue){
                $requestData[$changingKey] = $changingValue;
            }
        }
        // Grab upload rules from custom request
        // Validate input file fields
        if ($uploadRules){
            foreach ($uploadRules as $uploadKey => $uploadRule){
                $key = explode('.',$uploadKey);
                $key = $key[0];
                // Check if this is a file rule, by looking for the mimes rule
                if (isset($uploadRules[$key.".*"]) && GetRule($uploadRules[$key.".*"],'mimes')){
                    $required = GetRule($uploadRules[$key.".*"],'required') ? true : false;
                    $hasBeenFilled = array_key_exists($key,array_flip(array_keys($requestData)));
                    if ($required && !$hasBeenFilled){
                        $existingRecord = (Model() && Model()->{$key}) ? Model()->{$key} : false;
                        if (!$existingRecord){
                            return response()->json([
                                'message' => __('Missing file'),
                                'errors' => [
                                    'file' => [
                                        $requestMessages[$uploadKey.'.missingFile'] ?? __('Missing a required file. Please upload a file on this page.')
                                        ]
                                    ],
                                ], 422);
                        }
                    }
                }
                if (array_key_exists($key, $requestData)){
                    if (is_array($requestData[$key])){
                        foreach ($requestData[$key] as $x => $file){
                            // Check if file uploads have this applications URL in it
                            // If any upload doesnt have the url in its filename, then it has been tampered with
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
                            // Check if file exists in tmp folder
                            // Then move it to final public folder
                            // Strip APP_URL to locate this file locally
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
        }
        // Convert upload rules to string rules, otherwise the request will try to validate a mimetype on a path string
        foreach ($uploadRules as $key => $ruleArr) {
            $ruleArr = (is_string($ruleArr)) ? explode('|',$ruleArr) : $ruleArr;
            foreach ($ruleArr as $x => $rule) {
                if (preg_match('/(min|max|mime)/',$rule)){
                    unset($ruleArr[$x]);
                }
                if (!preg_match('/string/',json_encode($ruleArr))){
                    $ruleArr[] = 'string';
                }
            }
            $uploadRules[$key] = $ruleArr;
        }
        $requestRules = array_merge($requestRules,$uploadRules);

        $request = Validator::make($requestData, $requestRules, $requestMessages);
        $request = $request->validated();
        $afterValidation = method_exists($this->request,'afterValidation') ? $this->request->afterValidation($request) : false;
        // Merge with modified request from afterValidation()
        if ($afterValidation){
            foreach ($afterValidation as $modKey => $modValue){
                $request[$modKey] = $modValue;
            }
        }
        if ($createdOrUpdated === 'created'){
            ${$this->routePrefix} = (new $this->model)->create($request);
            if (method_exists($this->request,'afterCreate')){
                $this->request->afterCreate($request,${$this->routePrefix});
            }
            if (method_exists($this->request,'afterCreateOrUpdate')){
                $this->request->afterCreateOrUpdate($request,${$this->routePrefix});
            }
        } else if ($createdOrUpdated === 'updated') {
            ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);
            // Update any arrays / files 
            foreach ($request as $requestKey => $requestVal){
                // If request has an array, and points to storage, merge it with existing array if it has values already
                if (is_array($requestVal) && (strpos(implode(" ", $requestVal), '/storage/') !== false)) {
                    $newArr = [];
                    foreach ($requestVal as $val){
                        $newArr[] = $val;
                    }
                    try {
                        // check if object has an array for this already
                        $existing = ${$this->routePrefix}->$requestKey;
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
                    // If array limit is being overridden
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
                    // If array doesnt reach amount of required files
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
            ${$this->routePrefix}->update($request);
            if (method_exists($this->request,'afterUpdate')){
                $this->request->afterUpdate($request,${$this->routePrefix});
            }
            if (method_exists($this->request,'afterCreateOrUpdate')){
                $this->request->afterCreateOrUpdate($request,${$this->routePrefix});
            }
        }
        if (isset($filesToMove) && count($filesToMove) > 0){
            foreach ($filesToMove as $key => $file) {
                Storage::copy($file['oldPath'],$file['newPath']);
                Storage::delete($file['oldPath']);
            }
        }
        return $this->DCMSJSON(${$this->routePrefix},$createdOrUpdated);
    }

    public function store()
    {
        return $this->crud('created');
    }

    public function update($id)
    {
        $id = request()->route()->parameters[$this->routePrefix];
        return $this->crud('updated',$id);
    }

    public function destroy($id)
    {
        $this->__init();
        $model = ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);
        $passModel = $model;
        $model->delete();
        if (method_exists($this->request,'afterDelete')){
            $this->request->afterDelete($id,$passModel);
        }
    }

    public function DCMSJSON($object,$createdOrUpdated)
    {
        $this->__init();
        // Url
        $url = $this->{$createdOrUpdated.'Url'};
        preg_match_all('/__\S*__/m',$url,$matches);
        foreach($matches[0] as $match){
            $prop = str_replace('__','',$match);
            $url = str_replace($match,$object->$prop,$url);
        }
        if ((isset($this->createdUrl) && $createdOrUpdated == 'created') || (isset($this->updatedUrl) && $createdOrUpdated == 'updated')){
            if (request()->ajax()){
                $redirect = $url;
            } else {
                return redirect($url);
            }
        } else {
            if (request()->ajax()){
                $redirect = '/'.$this->routePrefix;
            } else {
                $redirect = redirect()->route($this->routePrefix.'.index');
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
