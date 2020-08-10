<?php

if (!function_exists('MaxSizeServer')) {
    function MaxSizeServer($type = 'mb')
    {
        $max_upload = (int) (ini_get('upload_max_filesize'));
        $max_post = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        switch ($type) {
            case 'mb':
                return $upload_mb;
                break;

            case 'bytes':
                return $upload_mb * pow(1024, 2);
                break;

            default:
                return $upload_mb;
                break;
        }
    }
}

if (!function_exists('GetPrefix')) {
    function GetPrefix()
    {
        return explode('/', request()->route()->uri)[0];
    }
}

if (!function_exists('GetClasses')) {
    function GetClasses()
    {
        $classes = [];
        foreach ($GLOBALS['classFolders'] as $folder) {
            foreach (scandir(base_path() . '/' . $folder) as $file) {
                if (strpos($file, '.php') !== false) {
                    $re = '/namespace \S*;/m';
                    $str = file_get_contents(base_path() . '/' . $folder . '/' . $file);
                    preg_match($re, $str, $namespace);
                    $namespace = str_replace('namespace ', '', $namespace[0]);
                    $namespace = str_replace(';', '', $namespace);
                    $file = str_replace('.php', '', $file);
                    array_push($classes, [
                        'file' => $file,
                        'class' => $namespace . '\\' . $file
                    ]);
                }
            }
        }
        return $classes;
    }
}

if (!function_exists('FindClass')) {
    function FindClass($prefix)
    {
        foreach (GetClasses() as $class) {
            if (strtolower($class['file']) == strtolower($prefix)) {
                return $class;
            }
        }
    }
}

if (!function_exists('Model')) {
    function Model($getName = null)
    {
        $routeParams = request()->route()->parameters();
        $model = ($routeParams !== null) ? reset($routeParams) : null;
        // Return model with attributes or return nothing if creating
        $model = ($model !== false) ? $model : null;
        // Return name if set as parameter
        $model = (isset($getName) && $getName == 'name') ? explode(".", \Request::route()->getName())[0] : $model;
        if (!is_object($model) && $model !== null){
            $prefix = GetPrefix();
            $class = FindClass($prefix)['class'];
            $model = $class::findOrFail($model);
        }
        return $model;
    }
}

if (!function_exists('FormMethod')) {
    // Which @method to return
    function FormMethod()
    {
        $routeName = \Request::route()->getName();
        $routeAction = explode(".", $routeName)[1];
        $formMethod = null;
        switch ($routeAction) {
            case 'create':
                $formMethod = 'POST';
                break;
            case 'edit':
            case 'update':
                $formMethod = 'PUT';
                break;
        }
        return $formMethod;
    }
}

if (!function_exists('RoutePrefix')) {
    // Return store or update route for form
    function RoutePrefix()
    {
        $routeName = \Request::route()->getName();
        try {
            $routeModel = explode(".", $routeName)[0];
            $routeAction = explode(".", $routeName)[1];
        } catch (\Throwable $th) {
            $routeModel = $routeName;
        }
        return $routeModel;
    }
}

if (!function_exists('FormRoute')) {
    // Return store or update route for form
    function FormRoute()
    {
        $routeName = \Request::route()->getName();
        $routeModel = explode(".", $routeName)[0];
        $routeAction = explode(".", $routeName)[1];
        $formRoute = null;
        switch ($routeAction) {
            case 'create':
                $formRoute = route($routeModel . '.store');
                break;
            case 'edit':
                $formRoute = route($routeModel . '.update', is_object(Model()) ? Model()->id : Model());
                break;
            case 'update':
                $formRoute = route($routeModel . '.update', is_object(Model()) ? Model()->id : Model());
                break;
        }
        return $formRoute;
    }
}

if (!function_exists('DeleteRoute')) {
    // Return delete route for form
    function DeleteRoute()
    {
        $routeName = \Request::route()->getName();
        $routeModel = explode(".", $routeName)[0];
        $model = is_object(Model()) ? Model()->id : Model();
        $deleteRoute = route($routeModel . '.destroy', $model);
        return $deleteRoute;
    }
}

// Images helpers

if (!function_exists('ValidateFile')) {
    function ValidateFile($key, $type = null, $maxFiles = 1, $maxSize = null)
    {
        $maxSize == null ? $maxSize = MaxSizeServer('bytes') : $maxSize;
        $msg = null;
        $status = 200;
        $files = request()->file($key);
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
            try {
                if (sizeof($files) > $maxFiles) {
                    $status = 422;
                    $msg = __('Too many files were uploaded at once.');
                }
            } catch (\Throwable $th) {
                //t
            }
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
                foreach (request()->file($key) as $file) {
                    $file->store('public/files/' . $type);
                }
                return '/storage/files/'.$type.'/'.$file->hashName();
            }
        } else {
            $msg = __('No file to process.');
            $status = 422;
        }
    }
}
