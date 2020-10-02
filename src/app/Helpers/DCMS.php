<?php

if (!function_exists('MaxSizeServer')) {
    function MaxSizeServer($type = 'mb')
    {
        $max_upload = (int) (ini_get('upload_max_filesize'));
        $max_post = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        switch ($type) {
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
        if(!app()->runningInConsole()){
            return explode('/', request()->route()->uri)[0];
        }
    }
}

if (!function_exists('GetClasses')) {
    function GetClasses()
    {
        $classes = [];
        foreach (config('dcms.classfolders') as $folder) {
            foreach (scandir(base_path() . '/' . $folder) as $file) {
                if (strpos($file, '.php') !== false) {
                    $re = '/namespace \S*;/m';
                    $str = file_get_contents(base_path() . '/' . $folder . '/' . $file);
                    preg_match($re, $str, $namespace);
                    $namespace = str_replace(array('namespace ', ';'), '', $namespace[0]);
                    $file = str_replace('.php', '', $file);
                    $classes[] = [
                        'file' => $file,
                        'class' => $namespace . '\\' . $file
                    ];
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
    function Model()
    {
        $class = request()->route()->controller->class;
        $prefix = request()->route()->controller->prefix;
        $id = (request()->route()->parameters()) ? request()->route()->parameters()[$prefix] : null;
        return $class::find($id);
    }
}

if (!function_exists('FormMethod')) {
    // Which @method to return
    function FormMethod()
    {
        $routeName = request()->route()->getName();
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
        $routeName = request()->route()->getName();
        try {
            $routeModel = explode(".", $routeName)[0];
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
        $routeName = request()->route()->getName();
        $routeModel = explode(".", $routeName)[0];
        $routeAction = explode(".", $routeName)[1];
        $formRoute = null;
        switch ($routeAction) {
            case 'create':
                $formRoute = route($routeModel . '.store');
                break;
            case 'update':
            case 'edit':
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
        $routeName = request()->route()->getName();
        $routeModel = explode(".", $routeName)[0];
        $model = is_object(Model()) ? Model()->id : Model();
        return route($routeModel . '.destroy', $model);
    }
}

if (!function_exists('RemoveDir')) {
    function RemoveDir($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!RemoveDir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}

if (!function_exists('CopyDir')) {
    function CopyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    CopyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}

if (!function_exists('RandomString')) {
    function RandomString($length = 30)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            } catch (Exception $e) {
            }
        }
        return $randomString;
    }
}

if (!function_exists('Flatten')) {
    function Flatten($array, $prefix = '')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result += Flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('ReflectClass')) {
    function ReflectClass($class)
    {
        $reflectionClass = new ReflectionClass($class);

        // File and line/length to generate content
        $filename = $reflectionClass->getFileName();
        $start_line = $reflectionClass->getStartLine() - 1;
        $end_line = $reflectionClass->getEndLine();
        $length = $end_line - $start_line;

        // Create body from start to end line
        $body = implode("", array_slice(file($filename), $start_line, $length));
        // Convert body to array to trim whitespace at end
        $body = preg_split("/\\r\\n|\\r|\\n/", $body);
        while ("" === end($body))
        {
            array_pop($body);
        }
        // Convert body to normal string again
        $body = implode("", array_slice(file($filename), $start_line, $length));

        $reflectionClass->body = $body;
        return $reflectionClass;
    }
}

if (!function_exists('ReflectCode')) {
    function ReflectCode($reflection)
    {
        // File and line/length to generate content
        $filename = $reflection->getFileName();
        $start_line = $reflection->getStartLine() - 1;
        $end_line = $reflection->getEndLine();
        $length = $end_line - $start_line;

        // Create body from start to end line
        $body = implode("", array_slice(file($filename), $start_line, $length));
        // Convert body to array to trim whitespace at end
        $body = preg_split("/\\r\\n|\\r|\\n/", $body);
        while ("" === end($body))
        {
            array_pop($body);
        }
        // Convert body to normal string again
        $body = implode("", array_slice(file($filename), $start_line, $length));

        $reflection->body = $body;
        return $reflection;
    }
}

if (!function_exists('WriteContent')) {
    function WriteContent($content, $line, $addContent)
    {
        // Convert content to array
        $content = preg_split("/\\r\\n|\\r|\\n/", $content);
        $content = array_values($content);
        // Trim content whitespace
        while ("" === end($content))
        {
            array_pop($content);
        }
        // Append content anywhere in this array
        $appendToRow = $line-1;
        array_splice($content, $appendToRow, 0,$addContent);

        $content = implode("\n",$content);

        return $content;
    }
}

if (!function_exists('AppendContent')) {
    function AppendContent($content, $offset, $addContent)
    {
        // Convert content to array
        $content = preg_split("/\\r\\n|\\r|\\n/", $content);
        $content = array_values($content);

        // Trim content whitespace
        while ("" === end($content))
        {
            array_pop($content);
        }

        // Append content anywhere in this array
        $appendToRow = count($content)-$offset;
        array_splice($content, $appendToRow, 0, $addContent);

        $content = implode("\n",$content);

        return $content;
    }
}
