<?php

namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Pveltrop\DCMS\Classes\Dropbox;
use Illuminate\Support\Facades\Storage;

trait DCMSCrud
{
    /**
     * Dynamic method to create/update a model
     *
     * @param $createdOrUpdated
     * @param $id
     * @return Application|JsonResponse|RedirectResponse|Redirector
     */
    public function persist($createdOrUpdated, $id=null)
    {
        $this->initDCMS();
        $this->requestData = request()->all();
        $this->requestRules = method_exists($this->request, 'rules') ? $this->request->rules() : false;

        $this->uploadRules = method_exists($this->request, 'uploadRules') ? $this->request->uploadRules() : false;
        $this->filesToMove = [];
        $this->filesToRemove = [];

        /**
         * Build upload rules & request data
         */

        $requestMessages = method_exists($this->request, 'messages') ? $this->request->messages() : false;

        // Merge request with modified request from beforeValidation()
        $beforeValidation = method_exists($this->request, 'beforeValidation') ? $this->request->beforeValidation($this->requestData) : false;
        if ($beforeValidation) {
            foreach ($beforeValidation as $changingKey => $changingValue) {
                $this->requestData[$changingKey] = $changingValue;
            }
        }

        /**
         * Validate final request
         */

        $this->requestRules = array_merge($this->uploadRules, $this->requestRules);

        $customRequest = new \Illuminate\Http\Request();
        $customRequest->setMethod('POST');
        $customRequest->request->add($this->requestData);

        $this->validate($customRequest, $this->requestRules, $requestMessages);
        $afterValidation = method_exists($this->request, 'afterValidation') ? $this->request->afterValidation($this->requestData) : false;

        /**
         * Remove tmp folder from array items in request
         */

        foreach ($this->uploadRules as $uploadKey => $uploadRule){
            $keysToFind = [
                str_replace('.*','',$uploadKey),
                str_replace('[]','',$uploadKey)
            ];
            foreach($keysToFind as $keyToFind){
                if (array_key_exists($keyToFind,$this->requestData)){
                    $files = $this->requestData[$keyToFind];
                    foreach ($files as $x => $file){
                        // Remove APP_URL since we will need the relative paths to move files around
                        $relativePath = str_replace([
                            env('APP_URL'),
                            'storage/'
                        ],[
                            '',
                            'public/'
                        ],$this->requestData[$keyToFind][$x]);

                        $this->filesToMove[$x]['oldPath'] = $relativePath;
                        $this->filesToMove[$x]['newPath'] = str_replace('tmp/','',$relativePath);

                        // Remove tmp folder from request data as well
                        $this->requestData[$keyToFind][$x] = str_replace('tmp/','',$this->requestData[$keyToFind][$x]);
                    }
                }
            }
        }

        if (count($this->filesToMove) > 0) {
            foreach ($this->filesToMove as $key => $file) {
                // Move file outside of tmp on Dropbox
                if ($this->storageConfig === 'dropbox') {
                    $findFileDropbox = Dropbox::findByPath($file['newPath']);
                    if ($findFileDropbox->status !== 200) {
                        $move = Dropbox::move($file['oldPath'], $file['newPath']);
                        // If file cant be moved from tmp to files folder
                        if ($move->status !== 200) {
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
                    // Move file outside of tmp on local server
                } elseif (!Storage::exists($file['newPath'])) {
                    Storage::copy($file['oldPath'], $file['newPath']);
                    Storage::delete($file['oldPath']);
                }
            }
        }

        // Merge with modified request from afterValidation()
        // This helps manipulating data before its being persisted
        if ($afterValidation) {
            foreach ($afterValidation as $modKey => $modValue) {
                $this->requestData[$modKey] = $modValue;
            }
        }
        // Create a new model, or update an existing one, and initialise afterFunctions
        // You can define these functions in the controller
        if ($createdOrUpdated === 'created') {
            ${$this->routePrefix} = (new $this->model)->create($this->requestData);
            if (method_exists($this, 'afterCreate')) {
                $this->afterCreate($this->requestData, ${$this->routePrefix});
            }
            if (method_exists($this, 'afterCreateOrUpdate')) {
                $this->afterCreateOrUpdate($this->requestData, ${$this->routePrefix});
            }
        } elseif ($createdOrUpdated === 'updated') {
            ${$this->routePrefix} = ((new $this->model)->find($id)) ?: (new $this->model)->find(request()->route()->parameters[$this->routePrefix]);

            /**
             * Remove any files/array items which are no longer being used
             */
            foreach ($this->uploadRules as $key => $ruleArr) {
                $keyToCheck = str_replace('.*', '', $key);
                // Check if file exists in model and request
                // If it exists in the model, and not in the request, delete the file
                if (isset($this->requestData[$keyToCheck])) {
                    foreach ($this->requestData[$keyToCheck] as $files) {
                        $modelFiles = ${$this->routePrefix}->{$keyToCheck};
                        $requestFiles = $this->requestData[$keyToCheck];

                        // If both properties are a string
                        if (is_string($requestFiles) && is_string($modelFiles) && $requestFiles !== $modelFiles) {
                            $this->filesToRemove[] = $requestFiles;
                        }

                        // If both properties are an array
                        if (is_array($requestFiles) && is_array($modelFiles)) {
                            foreach ($modelFiles as $fileKey => $modelFile) {
                                if (!in_array($modelFile, $requestFiles, true)) {
                                    $this->filesToRemove[] = $modelFile;
                                }
                            }
                        }
                    }
                }
            }

            ${$this->routePrefix}->update($this->requestData);
            if (method_exists($this, 'afterUpdate')) {
                $this->afterUpdate($this->requestData, ${$this->routePrefix});
            }
            if (method_exists($this, 'afterCreateOrUpdate')) {
                $this->afterCreateOrUpdate($this->requestData, ${$this->routePrefix});
            }
        }

        /*
        * Remove files from storage which haven't been passed in the request
        */
        if (count($this->filesToRemove) > 0) {
            foreach ($this->filesToRemove as $key => $file) {
                if ($this->storageConfig === 'dropbox') {
                    $file = Dropbox::findBySharedLink($file);
                    if ($file->status == 200) {
                        Dropbox::remove($file->response->path_lower);
                    }
                } else {
                    $file = str_replace('storage', 'public', $file);
                    if (Storage::exists($file)) {
                        Storage::delete($file);
                    }
                }
            }
        }

        return $this->DCMSJSON(${$this->routePrefix}, $createdOrUpdated);
    }

    public function DCMSJSON($object, $createdOrUpdated)
    {
        $this->initDCMS();
        // Url
        if ($this->{$createdOrUpdated.'Url'}) {
            $url = $this->{$createdOrUpdated.'Url'};
            $url = ReplaceWithAttr($url, $object);
            if ((isset($this->createdUrl) && $createdOrUpdated === 'created') || (isset($this->updatedUrl) && $createdOrUpdated === 'updated')) {
                if (request()->ajax()) {
                    $redirect = $url;
                } else {
                    return redirect($url);
                }
            } elseif (request()->ajax()) {
                $redirect = '/'.$this->routePrefix;
            } else {
                $redirect = redirect()->route($this->routePrefix.'.index');
            }
        } else {
            $url = null;
        }
        // Title
        $title = $this->{$createdOrUpdated.'Title'};
        $title = ReplaceWithAttr($title, $object);
        // Message
        $message = $this->{$createdOrUpdated.'Message'};
        $message = ReplaceWithAttr($message, $object);

        if ($url) {
            return response()->json([
                'title' => $title,
                'message' => $message,
                'url' => $redirect
            ], 200);
        } else {
            return response()->json([
                'title' => $title,
                'message' => $message,
            ], 200);
        }
    }
}
