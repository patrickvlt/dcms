<?php
namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait DCMSController
{
    public function DCMS(){
        // leave this empty
    }

    public function DCMSPrefix(){
        return (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
    }

    public function index()
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        
        $indexQuery = (isset($this->DCMS()['indexQuery'])) ? $this->DCMS()['indexQuery'] : FindClass($this->DCMSPrefix())['class']::all();
        if (request()->ajax()) {
            return $indexQuery;
        }
        $indexView = (isset($this->DCMS()['views']['index'])) ? $this->DCMS()['views']['index'] : 'index';

        $vars = method_exists($this,'beforeIndex') ? $this->beforeIndex() : null;
        return view($prefix.'.'.$indexView)->with($vars);
    }

    public function show($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $$prefix = $class::FindOrFail($id);
        $showView = (isset($this->DCMS()['views']['show'])) ? $this->DCMS()['views']['show'] : 'show';

        $vars = method_exists($this,'beforeShow') ? $this->beforeShow($id) : null;
        return view($prefix.'.'.$showView,compact($$prefix))->with($vars);
    }

    public function edit($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $$prefix = $class::FindOrFail($id);
        $editView = (isset($this->DCMS()['views']['edit'])) ? $this->DCMS()['views']['edit'] : 'edit';

        $vars = method_exists($this,'beforeEdit') ? $this->beforeEdit($id) : null;
        return view($prefix.'.'.$editView,compact($$prefix))->with($vars);
    }

    public function create()
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $createView = (isset($this->DCMS()['views']['create'])) ? $this->DCMS()['views']['create'] : 'create';

        $vars = method_exists($this,'beforeCreate') ? $this->beforeCreate() : null;
        return view($prefix.'.'.$createView)->with($vars);
    }

    public function crud($createdOrUpdated,$id=null)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $file = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['file'] : FindClass($prefix)['file'];

        $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $file.'Request';
        $classRequest = '\App\Http\Requests\\'.$requestFile;

        $requestData = request()->all();
        try {
            $modRequest = (new $classRequest())->beforeValidation();
            foreach ($modRequest as $modKey => $modValue){
                $requestData[$modKey] = $modValue;
            }
        } catch (\Throwable $th) {
            //
        }
        $request = Validator::make($requestData, (new $classRequest())->rules(), (new $classRequest())->messages());
        $request = $request->validated();
        if ($createdOrUpdated == 'created'){
            foreach ($request as $key => $val){
                // if value in current request is an array
                if (is_array($val)){
                    // if value is a file path
                    if (strpos(implode(" ",$val), '/storage/') !== false) {
                        $newArr = [];
                        // remove unnecessary quotes, make a new clean JSON array
                        foreach ($val as $x){
                            $x = str_replace('"','',$x);
                            array_push($newArr,$x);
                        }
                        $newArr = json_encode($newArr);
                        $newArr = str_replace('""','"',$newArr);
                        $request[$key] = $newArr;
                    }
                }
            }
            $$prefix = $class::create($request);
        } else if ($createdOrUpdated == 'updated') {
            $$prefix = $class::findOrFail($id);
                foreach ($request as $key => $val){
                    // if value in current request is an array
                    if (is_array($val)){
                        // if value is a file path
                        if (strpos(implode(" ",$val), '/storage/') !== false) {
                            $newArr = [];
                            // remove unnecessary quotes, make a new clean JSON array
                            foreach ($val as $x){
                                $x = str_replace('"','',$x);
                                array_push($newArr,$x);
                            }
                            try {
                                // check if object has an array for this already
                                $existing = json_decode($$prefix->$key,true);
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
                }
                $$prefix->update($request);
        }

        return $this->DCMSJSON($$prefix,$createdOrUpdated);
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
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $class::findOrFail($id)->delete();
    }

    public function DCMSJSON($object,$createdOrUpdated)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();

        if (isset($this->DCMS()[$createdOrUpdated]['url'])){
            if (request()->ajax()){
                $redirect = $this->DCMS()[$createdOrUpdated]['url'];
            } else {
                $redirect = redirect()->route($this->DCMS()[$createdOrUpdated]['url']);
            }
        } else {
            if (request()->ajax()){
                $redirect = '/'.$prefix;
            } else {
                $redirect = redirect()->route($prefix.'.index');
            }
        }
        // Title
        $title = (isset($this->DCMS()[$createdOrUpdated]['title'])) ? $this->DCMS()[$createdOrUpdated]['title'] : __(FindClass($prefix)['file']).__(' ').__($createdOrUpdated);
        preg_match_all('/__\S*__/m',$title,$matches);
        foreach($matches[0] as $match){
            $prop = str_replace('__','',$match);
            $title = str_replace($match,$object->$prop,$title);
        }
        // Message
        $message = (isset($this->DCMS()[$createdOrUpdated]['message'])) ? $this->DCMS()[$createdOrUpdated]['message'] : __(FindClass($prefix)['file']).' '.__('has been succesfully').' '.__($createdOrUpdated).'.';
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

    public function ProcessFile($type,$column)
    {
        $abort = false;
        foreach (request()->file() as $key => $file) {
            if ($file[0]->getSize() == false){
                $abort = true;
                break;
            }
        }
        if ($abort == true){
            return response()->json([
                'message' => __('Upload failed'),
                'errors' => [
                    'file' => [
                        __('File is above ').MaxSizeServer('mb').'MB.'
                    ]
                ]
            ], 422);
        }
        if ($abort == false){
            $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
            $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
            $file = FindClass($prefix)['file'];
            $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $file.'Request';
            $classRequest = '\App\Http\Requests\\'.$requestFile;

            $column = str_replace('[]','',$column);

            $uploadRules = (new $classRequest())->uploadRules();

            $request = Validator::make(request()->all(), $uploadRules,(new $classRequest())->messages());
            if ($request->failed()) {
                return response()->json([
                    'message' => __('Upload failed'),
                    'errors' => [
                        $request->errors()
                    ]
                ], 422);
            }
            else {
                $request = $request->validated();
                if (count($request) <= 0){
                    return response()->json([
                        'message' => __('Upload failed'),
                        'errors' => [
                            'file' => [
                                __('File couldn\'t get validated.')
                            ]
                        ]
                    ], 422);
                }
                $file = $request[$column][0];
                $file->store('public/files/' . $type.'/'.$column);
                $returnFile = '/storage/files/'.$type.'/'.$column.'/'.$file->hashName();
                return $returnFile;
            }
        }
    }

    public function DeleteFile($type,$column,$revertKey=null)
    {
        // Get route prefix and the class it belongs to
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];

        // Get column for request and folder structure
        $column = str_replace('[]','',$column);
        $path = str_replace('"','',stripslashes(request()->getContent()));

        // Get filename
        $name = explode('/',$path);
        $name = end($name);
        $name = explode('.',$name);
        unset($name[count($name)-1]);
        $name = $name[0];

        $file = 'public/files/'.$type.'/'.$column.'/'.$name;

        if (Storage::exists($file) == true){
            Storage::delete($file);
            $msg = 'Deleted succesfully';
        } 
        else if (Storage::exists($path)){
            Storage::delete($path);
            $msg = 'Deleted succesfully';
            $status = 200;
        }
        else {
            $msg = 'File doesn\'t exist';
            $status = 422;
        }

        // If a revert key was sent, use this to locate the value in the database, instead of the default column
        $column = ($revertKey) ? $revertKey : $column;
        $findInDB = $class::where($column,'like','%'.$name.'%')->get();
        // if the current class uses this file in any database row
        if (count($findInDB) > 0){
            // checking all rows using this file
            foreach ($findInDB as $key => $model){
                $colValue = $model->$column;
                // json decode the arrays with files
                $fileArr = json_decode($colValue,true);
                // find the file in the decoded array and remove it
                if (is_array($fileArr)){
                    foreach ($fileArr as $key => $dbFile) {
                        if ($dbFile == $path){
                            unset($fileArr[$key]);
                        }
                    }
                } else {
                    $fileArr = null;
                }
                // Double check if theres an array with files
                if (is_array($fileArr)){
                    if (count($fileArr) == 0){
                        $fileArr = null;
                    }
                }
                // Rewrite file array to insert in the database
                if ($fileArr !== null){
                    $fileArr = json_encode(array_values($fileArr));
                }
                $model->update([
                    $column => $fileArr
                ]);
            }
        }
        $msg = 'Deleted succesfully';
        $status = 200;
        return response()->json([$msg,$status]);
    }

    public function ImportSheet()
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $class.'Request';
        $classRequest = '\App\Http\Requests\\'.$requestFile;

        // which column belongs to which request attribute? e.g. 'name' => 1, 'created_at' => 5
        $importCols = (isset($this->DCMS()['import']['columns'])) ? $this->DCMS()['import']['columns'] : GetPrefix();

        $importData = request()->sheetData;
        //prepare sheet validation variables
        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');
        $x = 1;
        $errors = [];
        $failed = false;
        $nullableColumns = [];

        foreach ((new $classRequest())->rules() as $key => $rule){
            if (preg_match('/nullable/',$rule) || !preg_match('/required/',$rule)){
                array_push($nullableColumns,$key);
            }
        }

        if (!empty($importData)) {
            foreach ($importData as $row) {
                foreach ($row as $col){
                    // check if required columns arent empty
                    if ($col == null || $col == '' && !in_array($col,$nullableColumns)){
                        array_push($errors, ['line' => $x]);
                        $failed = true;
                    }
                }

                // if data is ready for validation, add to the request
                if ($failed == false) {
                    foreach ($importCols as $x => $col){
                        $validateData[$x] = $row[$col];
                    }
                    $customRequest->request->add($validateData);
                    $this->validate($customRequest, (new $classRequest())->rules(), (new $classRequest())->messages());
                }
                $x++;
            }
            // if failed, return a JSON response
            if ($failed == true) {
                return response()->json(['response' => [
                    'title' => (isset($this->DCMS()['import']['failed']['title'])) ? $this->DCMS()['import']['failed']['title'] : __('Import failed'),
                    'message' => (isset($this->DCMS()['import']['failed']['message'])) ? $this->DCMS()['import']['failed']['message'] : __('Some fields contain invalid data.'),
                ], 'errors' => $errors], 422);
            } else {
                //if succeeded, create objects and return a JSON response
                foreach ($importData as $row) {
                    $passedData = [];
                    //assign request keys to predefined keys to columns
                    foreach ($importCols as $x => $col){
                        $passedData[$x] = $row[$col];
                    }
                    $class::create($passedData);
                }
            }
        } else {
            return response()->json(['response' => [
                'title' => (isset($this->DCMS()['import']['empty']['title'])) ? $this->DCMS()['import']['empty']['title'] : __('Import failed'),
                'message' => (isset($this->DCMS()['import']['empty']['message'])) ? $this->DCMS()['import']['empty']['message'] : __('Please fill in data to import.'),
            ]], 422);
        }

        return response()->json(['response' => [
            'title' => (isset($this->DCMS()['import']['finished']['title'])) ? $this->DCMS()['import']['finished']['title'] : __('Import finished'),
            'message' => (isset($this->DCMS()['import']['finished']['message'])) ? $this->DCMS()['import']['finished']['message'] : __('All data has been succesfully imported.'),
        ], 'url' => '/address'], 200);
    }

    public function FixSheet()
    {
        // Get data from ajax request at jexcel table
        $data = request()->data;
        $th = request()->th;

        // Get data from controller, class and columns to use for autocorrection
        $columns = (isset($this->DCMS()['import']['autocorrect'])) ? $this->DCMS()['import']['autocorrect'] : null;        
        if ($columns == null){
            return false;
        }
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
            $column = searchForColumn($header['column'],$columns);
            // Find the class which belongs to the provided prefix, sent from jexcel
            $class = FindClass(strtolower($column))['class'];
            $class = new $class;
            // Loop through data the user has sent
            foreach ($data as $x => $row){
                // Make a query for each Table Header
                $query = $class::query();
                $fields = $columns[$column]['fields'];
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
