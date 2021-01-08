<?php

namespace App\Http\Controllers;

use Pveltrop\DCMS\Classes\Dropbox;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DCMSFilepondController extends Controller
{
    public function __construct()
    {
        if(!app()->runningInConsole()){
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
    }

    public function ProcessFile($prefix,$type,$column,$revertKey=null)
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
                        __('An error has occurred while trying to process the '.$key.' field.'),
                        __('File size exceeds global limit of ').MaxSizeServer('mb').'MB.'
                    ]
                ]
            ], 422);
        }
        if ($abort == false){
            $column = str_replace('[]','',$column);

            $allRules = (new $this->classRequest())->rules();
            $uploadRules = [];
            foreach ($allRules as $key => $ruleArr) {
                $ruleArr = (is_string($ruleArr)) ? explode('|',$ruleArr) : $ruleArr;
                foreach ($ruleArr as $x => $rule) {
                    if (preg_match('/(mimes|mimetypes)/',$rule)){
                        $uploadRules[$key] = $ruleArr;
                        continue;
                    }
                }
            }

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
            $path = '/tmp/files/' . $type.'/'.$column;

            // If using Dropbox for storage
            if (env('DCMS_STORAGE_SERVICE') == 'dropbox'){
                $dropbox = Dropbox::upload($file,$path);
                // If dropbox upload method returns a string (path to file which has been uploaded)
                if (is_string($dropbox)){
                    return $dropbox;
                } else {
                    return response()->json([
                        'message' => __('Upload failed'),
                        'errors' => [
                            'file' => [
                                __('Failed to upload file to Dropbox.')
                            ]
                        ]
                    ], 422);
                }
                // If using storage on webserver
            } else if (!env('DCMS_STORAGE_SERVICE')){
                $file->store('public/tmp/files/' . $type.'/'.$column);
                return env('APP_URL').'/storage/tmp/files/'.$type.'/'.$column.'/'.$file->hashName();
            }

        }
    }

    public function DeleteFile($column,$revertKey=null)
    {
        $msg = 'File doesn\'t exist';
        $status = 422;
        // Get route prefix and the class it belongs to
        $controller = '\App\Http\Controllers\\'.$this->file.'Controller';
        // Get column for request and folder structure
        $column = str_replace('[]','',$column);
        
        // If using Dropbox for storage
        if (env('DCMS_STORAGE_SERVICE') == 'dropbox'){
            $dropboxPath = Dropbox::findBySharedLink(request()->getContent());
            if ($dropboxPath->status == 200){
                $dropboxPath = $dropboxPath->response->path_lower;
                Dropbox::remove($dropboxPath);
                $msg = 'Deleted succesfully';
                $status = 200;
            }
        } else if (!env('DCMS_STORAGE_SERVICE')){
            // Convert path to variable in database, remove APP URL and strip slashes
            // Also rename storage to public, /storage is for Front End
            $path = str_replace('"','',stripslashes(request()->getContent()));
            $path = str_replace(env('APP_URL'),"",$path);
            $path = str_replace("/storage/","/public/",$path);
            if (Storage::exists($path)){
                $msg = 'Deleted succesfully';
                $status = 200;
                Storage::delete($path);
            }
        }
        
        return response()->json($msg,$status);
    }
}
