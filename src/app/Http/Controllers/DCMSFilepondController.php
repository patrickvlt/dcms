<?php

namespace Pveltrop\DCMS\Http\Controllers;

class DCMSFilepondController extends Controller
{
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
            $class = FindClass($prefix)['class'];
            $file = FindClass($prefix)['file'];
            dd($file);
            $requestFile = (isset($this->DCMS()['request'])) ? $this->DCMS()['request'] : $file.'Request';
            $classRequest = '\App\Http\Requests\\'.$requestFile;

            $column = str_replace('[]','',$column);

            $uploadRules = (new $classRequest())->uploadRules();

            $request = Validator::make(request()->all(), $uploadRules,(new $classRequest())->messages());
            if ($request->failed()) {
                return response()->json([
                    'message' => __('Upload failed'),
                    'errors' => [
                        $request->errors()
                    ]
                ], 422);
            }
            else {
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
                $returnFile = '/storage/files/'.$type.'/'.$column.'/'.$file->hashName();
                return $returnFile;
            }
        }
    }

    public function DeleteFile($type,$column,$revertKey=null)
    {
        // Get route prefix and the class it belongs to
        $prefix = (isset($this->DCMS()['routePrefix'])) ? $this->DCMS()['routePrefix'] : GetPrefix();
        $class = (isset($this->DCMS()['class'])) ? FindClass(strtolower($this->DCMS()['class']))['class'] : FindClass($prefix)['class'];

        // Get column for request and folder structure
        $column = str_replace('[]','',$column);
        $path = str_replace('"','',stripslashes(request()->getContent()));

        // Get filename
        $name = explode('/',$path);
        $name = end($name);
        $name = explode('.',$name);
        unset($name[count($name)-1]);
        $name = $name[0];

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
        $column = ($revertKey) ? $revertKey : $column;
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
                // Double check if theres an array with files
                if (is_array($fileArr)){
                    if (count($fileArr) == 0){
                        $fileArr = null;
                    }
                }
                // Rewrite file array to insert in the database
                if ($fileArr !== null){
                    $fileArr = json_encode(array_values($fileArr));
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
