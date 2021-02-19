<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Pveltrop\DCMS\Classes\Dropbox;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FilepondController extends Controller
{
    public function __construct()
    {
        if (!app()->runningInConsole()) {
            // Route prefix
            $this->prefix = request()->route()->prefix;
            // Get class file
            $this->file = FindClass($this->prefix)['file'];
            // Get class with namespace, by route prefix
            $this->class = FindClass($this->prefix)['class'];
            // Get class request with namespace
            $this->classRequest = '\App\Http\Requests\\'.($this->file . 'Request');
            // Check if DCMS config has a separate storage config for the current model
            $this->storageConfig = (config('dcms.storage.service.' . strtolower($this->prefix))) ?: config('dcms.storage.service.global');
        }
    }

    public function checkFileSizes(): void
    {
        $this->abort = false;
        
        foreach (request()->file() as $key => $file) {
            if ($file[0]->getSize() === false) {
                $this->abort = true;
                $this->key = $key;
                break;
            }
        }
    }

    public function makeUploadRules(): void
    {
        $allRules = (new $this->classRequest())->rules();
        $this->uploadRules = [];

        foreach ($allRules as $key => $ruleArr) {
            $ruleArr = (is_string($ruleArr)) ? explode('|', $ruleArr) : $ruleArr;
            foreach ($ruleArr as $x => $rule) {
                if (preg_match('/(mimes|mimetypes)/', $rule)) {
                    $this->uploadRules[$key] = $ruleArr;
                    continue;
                }
            }
        }
    }

    public function validateFile(): ?JsonResponse
    {
        $column = str_replace('[]', '', $this->column);
        $request = Validator::make(request()->all(), $this->uploadRules, (new $this->classRequest())->messages());
        $response = null;

        if ($request->failed()) {
            $response = response()->json([
                'message' => __('Upload failed'),
                'errors' => [
                    $request->errors()
                    ]
                ], 422);
        }
        $request = $request->validated();

        if (count($request) <= 0) {
            $response = response()->json([
                'message' => __('Upload failed'),
                'errors' => [
                    'file' => [
                        __('This file has no validation rules.')
                    ]
                ]
            ], 422);
        }

        if (!$response) {
            $this->file = $request[$column][0];
            $this->path = '/tmp/files/' . $this->type.'/'.$column;
        }

        return $response;
    }

    public function storeOnDropbox()
    {
        $dropbox = Dropbox::upload($this->file, $this->path);
        // If dropbox upload method returns a string (path to file which has been uploaded)
        if (is_string($dropbox)) {
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
    }

    public function ProcessFile($prefix, $type, $column, $revertKey=null)
    {
        MaxSizeServer();
        $this->prefix = $prefix;
        $this->type = $type;
        $this->column = $column;
        $this->revertKey = $revertKey;

        // Check if request doesnt contain a file which exceeds limit in php.ini
        $this->checkFileSizes();

        if ($this->abort === true) {
            return response()->json([
                'message' => __('Upload failed'),
                'errors' => [
                    'file' => [
                        __("Unable to retrieve the size of this file.")
                    ]
                ]
            ], 422);
        }

        if ($this->abort === false) {
            $this->makeUploadRules();

            // If validating file has a response (error), return this
            $validateFile = $this->validateFile();
            if ($validateFile) {
                return $validateFile;
            }

            // If using Dropbox for storage
            if ($this->storageConfig === 'dropbox') {
                return $this->storeOnDropbox();
            } else {
                // If using storage on webserver
                $this->file->store('public/tmp/files/' . $this->type.'/'.$this->column);
                $link = rtrim(env('APP_URL'), "/").'/storage/tmp/files/'.$this->type.'/'.$this->column.'/'.$this->file->hashName();
                return $link;
            }
        }
    }

    public function DeleteFile($column, $revertKey=null): JsonResponse
    {
        $msg = 'File doesn\'t exist';
        $status = 422;
        // Get column for request and folder structure
        $column = str_replace('[]', '', $column);

        // If using Dropbox for storage
        if ($this->storageConfig === 'dropbox') {
            $dropboxPath = Dropbox::findBySharedLink(request()->getContent());
            if ($dropboxPath->status === 200) {
                $dropboxPath = $dropboxPath->response->path_lower;
                Dropbox::remove($dropboxPath);
                $msg = 'Deleted succesfully';
                $status = 200;
            }
        } else {
            // Convert path to variable in database, remove APP URL and strip slashes
            // Also rename storage to public, /storage is for Front End
            $path = str_replace(array('"', env('APP_URL'), "/storage/"), array('', "", "/public/"), stripslashes(request()->getContent()));
            if (Storage::exists($path)) {
                $msg = 'Deleted succesfully';
                $status = 200;
                Storage::delete($path);
            }
        }

        return response()->json($msg, $status);
    }
}
