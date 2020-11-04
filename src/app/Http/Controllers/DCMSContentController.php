<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pveltrop\DCMS\Classes\Content;

class DCMSContentController extends Controller
{
    // protected $prefix;
    // protected $class;
    // protected $file;
    // protected $requestFile;
    // protected $classRequest;

    public function __construct()
    {
        // if(!app()->runningInConsole()){
            
        // }
    }

    public function update(Request $request)
    {
        $request = json_decode($request->getContent());
        $newContent = Content::updateOrCreate([
            'UUID'  => $request->elementUUID,
            'value' => $request->editorValue
        ]);
        dd($newContent);
    }
}
