<?php

namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Pveltrop\DCMS\Classes\Dropbox;
use Illuminate\Support\Facades\Storage;

trait DCMSCrud {
    public function makeUploadRules()
    {
        $this->uploadRules = [];
        foreach ($this->requestRules as $key => $ruleArr) {
            $ruleArr = (is_string($ruleArr)) ? explode('|',$ruleArr) : $ruleArr;
            foreach ($ruleArr as $x => $rule) {
                if (preg_match('/(mimes|mimetypes)/',$rule)){
                    $this->uploadRules[$key] = $ruleArr;
                    continue;
                }
            }
        }
    }

    /**
     * Dynamic method to create/update a model
     *
     * @param $createdOrUpdated
     * @param $id
     */
    public function crud($createdOrUpdated,$id=null)
    {
        $this->__init();
        $this->requestData = request()->all();
        $this->requestRules = method_exists($this->request,'rules') ? $this->request->rules() : false;

        $this->uploadRules = false;
        $this->filesToMove = [];
        $this->filesToRemove = [];

        /**
         * Build upload rules & request data
         */

        if($this->requestRules){
            $this->makeUploadRules();
        }
        $requestMessages = method_exists($this->request,'messages') ? $this->request->messages() : false;

        // Merge request with modified request from beforeValidation()
        $beforeValidation = method_exists($this->request,'beforeValidation') ? $this->request->beforeValidation($this->requestData) : false;
        if ($beforeValidation){
            foreach ($beforeValidation as $changingKey => $changingValue){
                $this->requestData[$changingKey] = $changingValue;
            }
        }

        /**
         * File validations
         */

        // Grab upload rules from custom request
        // Then loop through the requests' file rules
        if ($this->uploadRules){
            foreach ($this->uploadRules as $uploadKey => $uploadRule){
                $key = explode('.',$uploadKey);
                $key = $key[0];
                if (array_key_exists($key, $this->requestData)){
                    if (is_array($this->requestData[$key])){
                        foreach ($this->requestData[$key] as $x => $file){
                            // Check if file uploads have this applications URL in it
                            // If any upload doesnt have the url in its filename, then it has been tampered with
                            // Only check this if using local webserver storage
                            if (!preg_match('~'.rtrim(env('APP_URL'), "/").'~',$file) && preg_match('/http/',$file) && $this->storageConfig == 'laravel'){
                                return response()->json([
                                    'message' => __('Invalid file'),
                                    'errors' => [
                                        'file' => [
                                            //Example: logo.*.noRemote
                                            $requestMessages[$uploadKey.'.noRemote'] ?? __("The ".$key." field contains an invalid remote file. <br> Please use a different file.")
                                            ]
                                        ],
                                ], 422);
                            }
                            // Check if file exists in tmp folder
                            // Then move it to final public folder
                            if ($this->storageConfig == 'dropbox'){
                                $storedFile = false;
                                $findFile = Dropbox::findBySharedLink($file);
                                if ($findFile->status == 200){
                                    $storedFile = true;
                                    $oldPath = $findFile->response->path_lower;
                                    $newPath = str_replace('/tmp','',$findFile->response->path_lower);
                                }
                            } else {
                                // Strip APP_URL to locate this file locally on webserver
                                $oldPath = str_replace(rtrim(env('APP_URL'), "/"),'',$file);
                                $oldPath = str_replace('/storage/','/public/',$oldPath);
                                $newPath = str_replace('/tmp/','/',$oldPath);
                                $storedFile = Storage::exists($oldPath);
                                if ($storedFile && preg_match('/tmp/',$oldPath)){
                                    $this->requestData[$key][$x] = str_replace('/public/','/storage/',$newPath);
                                }
                            }
                            if ($storedFile){
                                $this->filesToMove[] = [
                                    'oldPath' => $oldPath,
                                    'newPath' => $newPath
                                ];
                            }
                            else {
                                return response()->json([
                                    'message' => __('Invalid file'),
                                    'errors' => [
                                        $key => [
                                            //Example: logo.*.notFound
                                            $requestMessages[$uploadKey.'.notFound'] ?? __("The ".$key." field contains a path to a file which doesn't exist. <br> Please upload a new file.")
                                        ]
                                    ],
                                ], 422);
                            }
                            // Check if a file is being assigned which actually belongs to this property
                            // For example: dont allow a thumbnail to be used for a banner
                            if (!preg_match('/'.$key.'/',$newPath)){
                                return response()->json([
                                    'message' => __('Invalid file'),
                                    'errors' => [
                                        'file' => [
                                            //Example: logo.*.fileType
                                            $requestMessages[$uploadKey.'.fileType'] ?? __("The ".$key." field contains a file which exists but can't be used by this field. <br> Please upload a new file.")
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
        foreach ($this->uploadRules as $key => $ruleArr) {
            $ruleArr = (is_string($ruleArr)) ? explode('|',$ruleArr) : $ruleArr;
            foreach ($ruleArr as $x => $rule) {
                if (preg_match('/(min|max|mime|mimetypes)/',$rule)){
                    unset($ruleArr[$x]);
                }
                if (!preg_match('/string/',json_encode($ruleArr))){
                    $ruleArr[] = 'string';
                }
            }
            $this->uploadRules[$key] = $ruleArr;
        }

        /**
         * Validate final request
         */

        $this->requestRules = array_merge($this->requestRules,$this->uploadRules);
        // Validate the final request
        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');
        $customRequest->request->add($this->requestData);
        $this->validate($customRequest, $this->requestRules, $requestMessages);
        $afterValidation = method_exists($this->request,'afterValidation') ? $this->request->afterValidation($this->requestData) : false;
        
        /**
         * (re)move files, then mass assign model and execute defined afterFunctions
         */
        
        // Move files from tmp to files folder
        if (count($this->filesToMove) > 0){
            foreach ($this->filesToMove as $key => $file) {
                if ($this->storageConfig == 'dropbox'){
                    $findFileDropbox = Dropbox::findByPath($file['newPath']);
                    if ($findFileDropbox->status !== 200){
                        $move = Dropbox::move($file['oldPath'],$file['newPath']);
                        // If file cant be moved from tmp to files folder
                        if ($move->status !== 200){
                            return response()->json([
                                'message' => __('Unable to persist file'),
                                'errors' => [
                                    'file' => [
                                        //Example: logo.*.cantPersist
                                        $requestMessages[$uploadKey.'.cantPersist'] ?? __("The ".$key." field contains a file which can't be persisted.")
                                        ]
                                    ],
                            ], 422);
                        }
                    }
                } else {
                    if (!Storage::exists($file['newPath'])){
                        Storage::copy($file['oldPath'],$file['newPath']);
                        Storage::delete($file['oldPath']);
                    }
                }
            }
        }

        // Merge with modified request from afterValidation()
        // This helps manipulating data before its being persisted
        if ($afterValidation){
            foreach ($afterValidation as $modKey => $modValue){
                $this->requestData[$modKey] = $modValue;
            }
        }
        // Create a new model, or update an existing one, and initialise afterFunctions
        // You can define these functions in the controller
        if ($createdOrUpdated === 'created'){
            ${$this->routePrefix} = (new $this->model)->create($this->requestData);
            if (method_exists($this,'afterCreate')){
                $this->afterCreate($this->requestData,${$this->routePrefix});
            }
            if (method_exists($this,'afterCreateOrUpdate')){
                $this->afterCreateOrUpdate($this->requestData,${$this->routePrefix});
            }
        } else if ($createdOrUpdated === 'updated') {
            ${$this->routePrefix} = ((new $this->model)->find($id)) ? (new $this->model)->find($id) : (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);

            // Remove any files which are no longer being used
            foreach ($this->uploadRules as $key => $ruleArr) {
                $keyToCheck = str_replace('.*','',$key);
                // Check if file exists in model and request
                // If it exists in the model, and not in the request, delete the file
                if (isset($this->requestData[$keyToCheck])){
                    foreach($this->requestData[$keyToCheck] as $files){
                        $modelFiles = ${$this->routePrefix}->{$keyToCheck};
                        $requestFiles = $this->requestData[$keyToCheck];
                        
                        // If both properties are a string
                        if (is_string($requestFiles) && is_string($modelFiles)){
                            if ($requestFiles !== $modelFiles){
                                $this->filesToRemove[] = $requestFiles;
                            }
                        }
    
                        // If both properties are an array
                        if (is_array($requestFiles) && is_array($modelFiles)){
                            foreach ($modelFiles as $fileKey => $modelFile) {
                                if (!in_array($modelFile,$requestFiles)){
                                    $this->filesToRemove[] = $modelFile;
                                }
                            }
                        }
                    }
                }
            }

            ${$this->routePrefix}->update($this->requestData);
            if (method_exists($this,'afterUpdate')){
                $this->afterUpdate($this->requestData,${$this->routePrefix});
            }
            if (method_exists($this,'afterCreateOrUpdate')){
                $this->afterCreateOrUpdate($this->requestData,${$this->routePrefix});
            }
        }

        // Remove files from storage which havent been passed in the request
        if (count($this->filesToRemove) > 0){
            foreach ($this->filesToRemove as $key => $file) {
                if ($this->storageConfig == 'dropbox'){
                    $file = Dropbox::findBySharedLink($file);
                    if ($file->status == 200){
                        Dropbox::remove($file->response->path_lower);
                    }
                } else {
                    $file = str_replace('storage','public',$file);
                    if (Storage::exists($file)){
                        Storage::delete($file);
                    }
                }
            }
        }

        return $this->DCMSJSON(${$this->routePrefix},$createdOrUpdated);
    }

    public function DCMSJSON($object,$createdOrUpdated)
    {
        $this->__init();
        // Url
        $url = $this->{$createdOrUpdated.'Url'};
        $url = ReplaceWithAttr($url,$object);

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
        $title = ReplaceWithAttr($title,$object);
        // Message
        $message = $this->{$createdOrUpdated.'Message'};
        $message = ReplaceWithAttr($message,$object);
        return response()->json([
            'title' => $title,
            'message' => $message,
            'url' => $redirect
        ], 200);
    }
}