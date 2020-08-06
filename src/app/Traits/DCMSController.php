<?php

namespace App\Traits;

include __DIR__ . '/../Helpers/DCMS.php';

$GLOBALS['classFolders'] = [
    'app'
];

trait DCMSController
{
    public function GetClasses()
    {
        $classes = [];
        foreach ($GLOBALS['classFolders'] as $folder){
            foreach (scandir(base_path().'/'.$folder) as $file){
                if (strpos($file, '.php') !== false) {
                    $re = '/namespace \S*;/m';
                    $str = file_get_contents(base_path().'/'.$folder.'/'.$file);
                    preg_match($re, $str, $namespace);
                    $namespace = str_replace('namespace ','',$namespace[0]);
                    $namespace = str_replace(';','',$namespace);
                    $file = str_replace('.php','',$file);
                    array_push($classes,[
                        'file' => $file,
                        'class' => $namespace.'\\'.$file
                    ]);
                }
            }
        }
        return $classes;
    }

    public function FindClass($prefix)
    {
        foreach ($this->GetClasses() as $class){
            if (strtolower($class['file']) == strtolower($prefix)){
                return $class;
            }
        }
    }

    public function destroy($id)
    {
        $prefix = explode('/',request()->route()->uri)[0];
        $class = $this->FindClass($prefix)['class'];
        if (request()->ajax()) {
            $class::findOrFail($id)->delete();
            return;
        } else {
            $class::findOrFail($id)->delete();
            return redirect()->route($prefix.'.index');
        }
    }
}