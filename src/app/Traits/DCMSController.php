<?php
namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

$GLOBALS['classFolders'] = [
    'app'
];

trait DCMSController
{
    public function DCMSPrefix(){
        return (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
    }

    public function DCMSClass(){
        return (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($this->DCMSPrefix())['class'];
    }

    public function DCMSModel(){
        return (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['file'] : FindClass($this->DCMSPrefix())['file'];
    }

    public function index()
    {
        $indexQuery = (isset($this->DCMS()['indexQuery'])) ? $this->DCMS()['indexQuery'] : FindClass($this->DCMSPrefix())['class']::all();
        if (request()->ajax()) {
            return $indexQuery;
        }
        $indexView = (isset($this->DCMS()['views']['index'])) ? $this->DCMS()['views']['index'] : 'index';
        return view($this->DCMSPrefix().'.'.$indexView);
    }

    public function show($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $$prefix = $class::FindOrFail($id);
        $showView = (isset($this->DCMS()['views']['show'])) ? $this->DCMS()['views']['show'] : 'show';
        return view($prefix.'.'.$showView)->with([
            $prefix => $$prefix
        ]);
    }

    public function edit($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
        $$prefix = $class::FindOrFail($id);
        $showView = (isset($this->DCMS()['views']['edit'])) ? $this->DCMS()['views']['edit'] : 'edit';

        return view($prefix.'.'.$showView)->with([
            $prefix => $$prefix
        ]);
    }

    public function create()
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $createView = (isset($this->DCMS()['views']['create'])) ? $this->DCMS()['views']['create'] : 'create';
        return view($prefix.'.'.$createView);
    }

    public function crud($createdOrUpdated,$id=null)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];

        $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $class.'Request';
        $classRequest = '\App\Http\Requests\\'.$requestFile;

        $requestData = request()->all();
        try {
            $modRequest = (new $classRequest())->beforeValidation();
            foreach ($modRequest as $modKey => $modValue){
                $requestData[$modKey] = $modValue;
            }
        } catch (\Throwable $th) {
            //throw $th;
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
            // $$prefix = $class::create($request);
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
            return response()->json(__('File is above ').MaxSizeServer('mb').'MB.',422);
        }
        if ($abort == false){
            $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
            $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];
            $file = FindClass($prefix)['file'];

            $column = str_replace('[]','',$column);

            $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $class.'Request';
            $classRequest = '\App\Http\Requests\\'.$requestFile;
            $uploadRules = (new $classRequest())->uploadRules();

            $request = Validator::make(request()->all(), $uploadRules,(new $classRequest())->messages());

            if ($request->fails()){
                return response()->json($request->errors(),422);
            }
            else {
                $request = $request->validated();
                $file = $request[$column][0];
                $file->store('public/files/' . $type.'/'.$column);
                $returnFile = '/storage/files/'.$type.'/'.$column.'/'.$file->hashName();
                return $returnFile;
            }
        }
    }

    public function DeleteFile($type,$column)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];

        $column = str_replace('[]','',$column);
        $path = str_replace('"','',stripslashes(request()->getContent()));
        $name = explode('/',$path);
        $name = end($name);
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
                if (is_array($fileArr)){
                    if (count($fileArr) == 0){
                        $fileArr = null;
                    }
                }
                if ($fileArr !== null){
                    $fileArr = json_encode(array_values($fileArr));
                }
                $model->update([
                    $column => $fileArr
                ]);
            }
            $msg = 'Deleted succesfully';
            $status = 200;
        }
        return response()->json([$msg,$status]);
    }
}