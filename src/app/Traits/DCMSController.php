<?php
namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait DCMSController
{
    protected $prefix;
    protected $class;
    protected $file;
    protected $requestFile;
    protected $classRequest;
    protected $indexQuery;
    protected $indexView;
    protected $showView;
    protected $editView;
    protected $createView;
    protected $createdUrl;
    protected $createdTitle;
    protected $createdMessage;
    protected $updatedUrl;
    protected $updatedTitle;
    protected $updatedMessage;
    protected $deletedUrl;
    protected $deletedTitle;
    protected $deletedMessage;
    protected $importCols;
    protected $importFailedTitle;
    protected $importFailedMessage;
    protected $importEmptyTitle;
    protected $importEmptyMessage;
    protected $importFinishedTitle;
    protected $importFinishedMessage;
    protected $importedUrl;
    protected $autoFixColumns;

    // This returns void by default
    public function DCMS(): void {}

    public function __construct()
    {
        // Route prefix
        $this->prefix = $this->DCMS()['routePrefix'] ?? GetPrefix();
        // Get class file
        $this->file = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['file'] : FindClass($this->prefix)['file'];
        // Get class with namespace, by route prefix
        $this->class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($this->prefix)['class'];
        // Get class request file
        $this->requestFile = $this->DCMS()['request'] ?? ($this->file . 'Request');
        // Get class request with namespace
        $this->classRequest = '\App\Http\Requests\\'.$this->requestFile;
        // Default index query
        $this->indexQuery = $this->DCMS()['indexQuery'] ?? $this->class::all();
        // CRUD views
        $this->indexView = $this->DCMS()['views']['index'] ?? 'index';
        $this->showView = $this->DCMS()['views']['show'] ?? 'show';
        $this->editView = $this->DCMS()['views']['edit'] ?? 'edit';
        $this->createView = $this->DCMS()['views']['create'] ?? 'create';
        // JSON CRUD responses
        $this->createdUrl = $this->DCMS()['created']['url'] ?? null;
        $this->createdTitle = $this->DCMS()['created']['title'] ?? __($this->file).__(' ').__('created');
        $this->createdMessage = $this->DCMS()['created']['message'] ?? __($this->file).__(' ').__('has been successfully created');
        $this->updatedUrl = $this->DCMS()['updated']['url'] ?? null;
        $this->updatedTitle = $this->DCMS()['updated']['title'] ?? __($this->file).__(' ').__('updated');
        $this->updatedMessage = $this->DCMS()['updated']['message'] ?? __($this->file).__(' ').__('has been successfully updated');
        $this->deletedUrl = $this->DCMS()['deleted']['url'] ?? null;
        $this->deletedTitle = $this->DCMS()['deleted']['title'] ?? __($this->file).__(' ').__('deleted');
        $this->deletedMessage = $this->DCMS()['deleted']['message'] ?? __($this->file).__(' ').__('has been successfully deleted');
        // jExcel imports
        $this->importCols = $this->DCMS()['import']['columns'] ?? new \RuntimeException("No columns defined for jExcel imports.");
        $this->importFailedTitle = $this->DCMS()['import']['failed']['title'] ?? __('Import failed');
        $this->importFailedMessage = $this->DCMS()['import']['failed']['message'] ?? __('Some fields contain invalid data.');
        $this->importEmptyTitle = $this->DCMS()['import']['empty']['title'] ?? __('Import failed');
        $this->importEmptyMessage = $this->DCMS()['import']['empty']['message'] ?? __('Please fill in data to import.');
        $this->importFinishedTitle = $this->DCMS()['import']['finished']['title'] ?? __('Import finished');
        $this->importFinishedMessage = $this->DCMS()['import']['finished']['message'] ?? __('All data has been succesfully imported.');
        $this->importedUrl = $this->DCMS()['imported']['url'] ?? null;
        // jExcel autocorrect columns
        $this->autoFixColumns = $this->DCMS()['import']['autocorrect'] ?? null;
    }

    // The three functions below are for the Model helper function in DCMS.php
    public function DCMSPrefix(){
        return $this->DCMS()['routePrefix'] ?? GetPrefix();
    }

    public function DCMSClass(){
        return (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($this->DCMSPrefix())['class'];
    }

    public function DCMSModel(){
        return (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['file'] : FindClass($this->DCMSPrefix())['file'];
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->indexQuery;
        }
        $vars = method_exists($this,'beforeIndex') ? $this->beforeIndex() : null;
        return view($this->prefix.'.'.$this->indexView)->with($vars);
    }

    public function show($id)
    {
        ${$this->prefix} = $this->class::FindOrFail($id);

        $vars = method_exists($this,'beforeShow') ? $this->beforeShow($id) : null;
        return view($this->prefix.'.'.$this->showView,compact(${$this->prefix}))->with($vars);
    }

    public function edit($id)
    {
        ${$this->prefix} = $this->class::FindOrFail($id);

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
        try {
            $modRequest = (new $this->classRequest())->beforeValidation();
            foreach ($modRequest as $modKey => $modValue){
                $requestData[$modKey] = $modValue;
            }
        } catch (\Throwable $th) {
            //
        }
        $request = Validator::make($requestData, (new $this->classRequest())->rules(), (new $this->classRequest())->messages());
        $request = $request->validated();
        if ($createdOrUpdated === 'created'){
            // This is for files
            foreach ($request as $key => $val){
                // If request has an array, and points to storage, convert it to a JSON array
                if (is_array($val) && strpos(implode(" ", $val), '/storage/') !== false) {
                    $newArr = [];
                    // remove unnecessary quotes, make a new clean JSON array
                    foreach ($val as $x){
                        $x = str_replace('"','',$x);
                        $newArr[] = $x;
                    }
                    $newArr = json_encode($newArr);
                    $newArr = str_replace('""','"',$newArr);
                    $request[$key] = $newArr;
                }
            }
            ${$this->prefix} = $this->class::create($request);
        } else if ($createdOrUpdated === 'updated') {
            ${$this->prefix} = $this->class::findOrFail($id);
                // This is for files
                foreach ($request as $key => $val){
                    // If request has an array, and points to storage, convert it to a JSON array
                    if (is_array($val) && strpos(implode(" ", $val), '/storage/') !== false) {
                        $newArr = [];
                        // remove unnecessary quotes, make a new clean JSON array
                        foreach ($val as $x){
                            $x = str_replace('"','',$x);
                            $newArr[] = $x;
                        }
                        try {
                            // check if object has an array for this already
                            $existing = json_decode(${$this->prefix}->$key,true);
                            if(count($existing) > 0){
                                $newArr = array_merge($existing,$newArr);
                            }
                        } catch (\Throwable $th) {
                            //
                        }
                        $newArr = json_encode($newArr);
                        $newArr = str_replace('""','"',$newArr);

                        $request[$key] = $newArr;
                    }
                }
                ${$this->prefix}->update($request);
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
        $this->class::findOrFail($id)->delete();
    }

    public function DCMSJSON($object,$createdOrUpdated)
    {
        if (isset($this->createdUrl)){
            if (request()->ajax()){
                $redirect = $this->{$createdOrUpdated.'Url'};
            } else {
                $redirect = redirect()->route($this->{$createdOrUpdated.'Url'});
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

//    public function ProcessFile($type,$column)
//    {
//        $abort = false;
//        foreach (request()->file() as $key => $file) {
//            if ($file[0]->getSize() == false){
//                $abort = true;
//                break;
//            }
//        }
//        if ($abort == true){
//            return response()->json([
//                'message' => __('Upload failed'),
//                'errors' => [
//                    'file' => [
//                        __('File is above ').MaxSizeServer('mb').'MB.'
//                    ]
//                ]
//            ], 422);
//        }
//        if ($abort == false){
//            $column = str_replace('[]','',$column);
//
//            $uploadRules = (new $this->classRequest())->uploadRules();
//
//            $request = Validator::make(request()->all(), $uploadRules,(new $this->classRequest())->messages());
//            if ($request->failed()) {
//                return response()->json([
//                    'message' => __('Upload failed'),
//                    'errors' => [
//                        $request->errors()
//                    ]
//                ], 422);
//            }
//
//            $request = $request->validated();
//            if (count($request) <= 0){
//                return response()->json([
//                    'message' => __('Upload failed'),
//                    'errors' => [
//                        'file' => [
//                            __('File couldn\'t get validated.')
//                        ]
//                    ]
//                ], 422);
//            }
//            $file = $request[$column][0];
//            $file->store('public/files/' . $type.'/'.$column);
//            return '/storage/files/'.$type.'/'.$column.'/'.$file->hashName();
//        }
//    }
//
//    public function DeleteFile($type,$column,$revertKey=null)
//    {
//        // Get column for request and folder structure
//        $column = str_replace('[]','',$column);
//        $path = str_replace('"','',stripslashes(request()->getContent()));
//
//        // Get filename
//        $name = explode('/',$path);
//        $name = end($name);
//        $name = explode('.',$name);
//        unset($name[count($name)-1]);
//        $name = $name[0];
//
//        $file = 'public/files/'.$type.'/'.$column.'/'.$name;
//
//        if (Storage::exists($file) == true){
//            Storage::delete($file);
//            $msg = 'Deleted succesfully';
//        }
//        else if (Storage::exists($path)){
//            Storage::delete($path);
//            $msg = 'Deleted succesfully';
//            $status = 200;
//        }
//        else {
//            $msg = 'File doesn\'t exist';
//            $status = 422;
//        }
//
//        // If a revert key was sent, use this to locate the value in the database, instead of the default column
//        $column = ($revertKey) ?: $column;
//        $findInDB = $this->class::where($column,'like','%'.$name.'%')->get();
//        // if the current class uses this file in any database row
//        if (count($findInDB) > 0){
//            // checking all rows using this file
//            foreach ($findInDB as $key => $model){
//                $colValue = $model->$column;
//                // json decode the arrays with files
//                $fileArr = json_decode($colValue,true);
//                // find the file in the decoded array and remove it
//                if (is_array($fileArr)){
//                    foreach ($fileArr as $y => $dbFile) {
//                        if ($dbFile == $path){
//                            unset($fileArr[$y]);
//                        }
//                    }
//                } else {
//                    $fileArr = null;
//                }
//                // Double check if theres an array with files
//                if (is_array($fileArr)){
//                    if (count($fileArr) == 0){
//                        $fileArr = null;
//                    }
//                }
//                // Rewrite file array to insert in the database
//                if ($fileArr !== null){
//                    $fileArr = json_encode(array_values($fileArr));
//                }
//                $model->update([
//                    $column => $fileArr
//                ]);
//            }
//        }
//        $msg = 'Deleted succesfully';
//        $status = 200;
//        return response()->json([$msg,$status]);
//    }

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

        foreach ((new $this->classRequest())->rules() as $key => $rule){
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
                    $this->validate($customRequest, (new $this->classRequest())->rules(), (new $this->classRequest())->messages());
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
                $this->class::create($passedData);
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
