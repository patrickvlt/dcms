<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function ProcessFile($type)
    {
        $file = request()->file();

        // Get this from the controller array
        // $maxSize = MaxSizeServer('bytes');
        // $maxFiles = ''

        $msg = null;
        $status = 200;
        $files = request()->file(array_key_first($file));
        //Extensions
        switch ($type) {
            case 'image':
                $extensions = [
                    'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg', 'image/webp'
                ];
                break;
            case 'sheet':
                $extensions = [
                    'application/octet-stream', 'application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];
                break;
        }
        if (!empty($files)) {
            // try {
            //     if (sizeof($files) > $maxFiles) {
            //         $status = 422;
            //         $msg = __('Too many files were uploaded at once.');
            //     }
            // } catch (\Throwable $th) {
            //     //t
            // }
            $msg = __('Upload succeeded');
            $status = 200;
            $processedFiles = [];
            //Loop through sent files
            foreach ($files as $file) {
                $file = (is_array($file)) ? $file[0] : $file;
                //Validate the uploaded file
                try {
                    if ((int) $file->getSize() > (int) $maxSize) {
                        $msg = __('File size can\'t be above ' . MaxSizeServer('mb') . 'MB.');
                        $status = 422;
                    }
                    if (!in_array($file->getMimeType(), $extensions)) {
                        $msg = __('File is invalid.');
                        $status = 422;
                    }
                } catch (\Throwable $th) {
                    $msg = __('Invalid file or it\'s size is above ' . MaxSizeServer('mb') . 'MB.');
                    $status = 422;
                    break;
                }
                if ($status == 422) {
                    break;
                }
            }
            if ($status == 422) {
                return response()->json(['message' => $msg], $status);
            } else {
                foreach (request()->file(array_key_first($file)) as $file) {
                    $file->store('public/files/' . $type);
                }
                return '/storage/files/'.$type.'/'.$file->hashName();
            }
        } else {
            $msg = __('No file to process.');
            $status = 422;
        }
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
            $status = 422;
        }
        return response()->json([$msg,$status]);
    }
}
