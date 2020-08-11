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
    public function index()
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $indexQuery = (isset($this->DCMS()['indexQuery'])) ? $this->DCMS()['indexQuery'] : FindClass($prefix)['class']::all();
        if (request()->ajax()) {
            return $indexQuery;
        }
        $indexView = (isset($this->DCMS()['views']['index'])) ? $this->DCMS()['views']['index'] : 'index';
        return view($prefix.'.'.$indexView);
    }

    public function show($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = FindClass($prefix)['class'];
        $$prefix = $class::FindOrFail($id);
        $showView = (isset($this->DCMS()['views']['show'])) ? $this->DCMS()['views']['show'] : 'show';
        return view($prefix.'.'.$showView)->with([
            $prefix => $$prefix
        ]);
    }

    public function edit($id)
    {
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = FindClass($prefix)['class'];
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
        $class = FindClass($prefix)['class'];

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
                if (is_array($val)){
                    $val = json_encode($val);
                    $val = stripslashes($val);
                    $val = str_replace('""','"',$val);
                    $request[$key] = $val;
                }
            }
            $$prefix = $class::create($request);
        } else if ($createdOrUpdated == 'updated') {
            $$prefix = $class::findOrFail($id);
                foreach ($request as $key => $val){
                    if (is_array($val)){
                        $existing = json_decode(stripslashes($$prefix->$key));
                        if ($$prefix->key !== null){
                            $request[$key] = array_merge(json_decode(stripslashes($$prefix->$key)),$val);
                        } else {
                            $request[$key] = json_encode($val);
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
        $class = FindClass($prefix)['class'];
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
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = FindClass($prefix)['class'];
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
            return response()->json('/storage/files/'.$type.'/'.$column.'/'.$file->hashName(),200,[],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        }
    }

    public function DeleteFile($type,$column)
    {
        $column = str_replace('[]','',$column);
        $path = str_replace('"','',stripslashes(request()->getContent()));
        $name = explode('/',$path);
        $name = end($name);
        $file = 'public/files/'.$type.'/'.$column.'/'.$name;
        $fileExists = Storage::exists($file);
        if ($fileExists == true){
            Storage::delete($file);
            $msg = 'Deleted succesfully';
            $status = 200;
        } else {
            $msg = 'File doesn\'t exist';
            $status = 422;
        }
        return response()->json([$msg,$status]);
    }
}