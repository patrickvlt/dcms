<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DCMSFilepondController extends Controller
{
    protected $prefix;
    protected $class;
    protected $file;
    protected $requestFile;
    protected $classRequest;

    public function __construct()
    {
        // Route prefix
        $this->prefix = request()->route()->prefix;
        // Get class file
        $this->file = FindClass($this->prefix)['file'];
        // Get class with namespace, by route prefix
        $this->class = FindClass($this->prefix)['class'];
        // Get class request file
        $this->requestFile = ($this->file . 'Request');
        // Get class request with namespace
        $this->classRequest = '\App\Http\Requests\\'.$this->requestFile;
    }

    public function ProcessFile($prefix,$type,$column)
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
            $column = str_replace('[]','',$column);

            $uploadRules = (new $this->classRequest())->uploadRules();

            $request = Validator::make(request()->all(), $uploadRules,(new $this->classRequest())->messages());
            if ($request->failed()) {
                return response()->json([
                    'message' => __('Upload failed'),
                    'errors' => [
                        $request->errors()
                    ]
                ], 422);
            }

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
            return '/storage/files/'.$type.'/'.$column.'/'.$file->hashName();
        }
    }

    public function DeleteFile($prefix,$type,$column,$revertKey=null)
    {
        // Get route prefix and the class it belongs to
        $controller = '\App\Http\Controllers\\'.$this->file.'Controller';

        // Get column for request and folder structure
        $column = str_replace('[]','',$column);
        $path = str_replace('"','',stripslashes(request()->getContent()));

        // Get filename
        $name = explode('/',$path);
        $name = end($name);
        // Without extension for DB
        $dbName = explode('.',$name);
        unset($dbName[count($dbName)-1]);
        $dbName = $dbName[0];

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
        $column = ($revertKey) ?: $column;
        $findInDB = $this->class::where($column,'like','%'.$dbName.'%')->get();
        // if the current class uses this file in any database row
        if (count($findInDB) > 0){
            // checking all rows using this file
            foreach ($findInDB as $key => $model){
                // Check if column is an array or string, then remove this
                $fileArr = $model->$column;
                if (is_array($fileArr)){
                    foreach ($fileArr as $y => $dbFile) {
                        if ($dbFile == $path){
                            unset($fileArr[$y]);
                        }
                    }
                } else {
                    $fileArr = null;
                }
                // Double check if theres an array with files
                if (is_array($fileArr) && count($fileArr) == 0) {
                    $fileArr = null;
                }
                // Rewrite file array to insert in the database
                if ($fileArr !== null){
                    $fileArr = array_values($fileArr);
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
}
