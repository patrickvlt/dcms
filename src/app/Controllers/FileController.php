<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function ProcessFile($type)
    {
        $file = request()->file();
        return ValidateFile(array_key_first($file),$type);
    }

    public function DeleteFile($type)
    {
        $file = 'public/files/'.$type.'/'.request()->getContent();
        $fileExists = Storage::exists($file);
        if ($fileExists == true){
            Storage::delete($file);
            $msg = 'Deleted succesfully';
            $status = 200;
        } else {
            $msg = 'File doesn\'t exist';
            $status = 200;
        }
        return response()->json([$msg,$status]);
    }
}
