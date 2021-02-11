<?php

use Illuminate\Support\Facades\Schema;
use Pveltrop\DCMS\Classes\Content;

/**
 * Return max file size, defined in php.ini
 */
if (!function_exists('MaxSizeServer')) {
    function MaxSizeServer($type = 'mb')
    {
        $max_upload = (int) (ini_get('upload_max_filesize'));
        $max_post = (int) (ini_get('post_max_size'));
        $upload_mb = min($max_upload, $max_post);
        switch ($type) {
            case 'bytes':
                return $upload_mb * pow(1024, 2);
                break;

            case 'kb':
                return $upload_mb * 1000;
                break;

            default:
                return $upload_mb;
                break;
        }
    }
}

/**
 * Get route prefix for current model
 */
if (!function_exists('GetPrefix')) {
    function GetPrefix()
    {
        if (!app()->runningInConsole()) {
            return explode('/', request()->route()->uri)[0];
        }
    }
}

/**
 * Get all models
 */
if (!function_exists('GetModels')) {
    function GetModels()
    {
        $classes = [];
        foreach (config('dcms.modelFolders') as $folder) {
            foreach (scandir(base_path() . '/' . $folder) as $file) {
                if (preg_match('/\.php/',$file)) {
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

/**
 * Find class which belongs to a certain prefix, names have to be identical
 */
if (!function_exists('FindClass')) {
    function FindClass($prefix)
    {
        foreach (GetModels() as $class) {
            if (strtolower($class['file']) == strtolower($prefix)) {
                return $class;
            }
        }
        throw new \RuntimeException("Couldn't find Model which belongs to: " . $prefix . ". Make sure to configure the Models folder in config/dcms.php");
    }
}

/**
 * Grab current model
 */
if (!function_exists('Model')) {
    function Model()
    {
        $model = request()->route()->controller->model;
        $routePrefix = request()->route()->controller->routePrefix;
        $id = (request()->route()->parameters()) ? request()->route()->parameters()[$routePrefix] : null;
        return $model::find($id);
    }
}

/**
 * Return correct Form method, based on current route
 */
if (!function_exists('FormMethod')) {
    // Which @method to return
    function FormMethod()
    {
        $routeName = request()->route()->getName();
        $routeAction = explode(".", $routeName);
        $routeAction = end($routeAction);
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

/**
 * Generate correct formroute, if Laravel conventions are correct
 */
if (!function_exists('FormRoute')) {
    // Return store or update route for form
    function FormRoute($prefix = null)
    {
        $routeName = request()->route()->getName();
        if (!$prefix) {
            $prefix = explode(".", $routeName)[0];
        }
        $routeAction = explode(".", $routeName);
        $routeAction = end($routeAction);
        $formRoute = null;

        // Try to append parameters with Laravels route helper
        if (!function_exists('fixRoute')) {
            function fixRoute($prefix, $action,$routeName)
            {
                $addParameters = [];
                foreach (request()->route()->parameters as $key => $parameter) {
                    if (isset(request()->route()->parameters[$prefix]) && $key == request()->route()->parameters[$prefix]) {
                        continue;
                    } else {
                        $addParameters[] = $parameter;
                    }
                }
                try {
                    return route($prefix . $action, $addParameters);
                } catch (\Throwable $th) {
                    preg_match_all('/(\S*)(\.edit|\.create)/m',$routeName,$matches,PREG_SET_ORDER, 0);
                    $prefix = $matches[0][1];
                    return route($prefix . $action, $addParameters);
                }
            }
        }

        switch ($routeAction) {
            case 'create':
                try {
                    $formRoute = route($prefix . '.store', request()->route()->parameters[$prefix]);
                } catch (\Throwable $th) {
                    $formRoute = fixRoute($prefix, '.store',$routeName);
                }
                break;
            case 'update':
            case 'edit':
                try {
                    $formRoute = route($prefix . '.update', request()->route()->parameters[$prefix]);
                } catch (\Throwable $th) {
                    $formRoute = fixRoute($prefix, '.update',$routeName);
                }
                break;
        }
        return $formRoute;
    }
}

/**
 * Get the routeprefix from current model
 */
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

/**
 * Return current route's name
 */
if (!function_exists('CurrentRoute')) {
    function CurrentRoute()
    {
        $routeName = request()->route()->getAction()['as'] ?? null;
        return $routeName;
    }
}

/**
 * Return delete route which belongs to form
 */
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

/**
 * Remove a directory
 */
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

/**
 * Copy a directory
 */
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


/**
 * Generate a random string, useful for tokens or links
 */
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

/**
 * Reflect and clean code, to insert content easily and prevent whitespace problems
 */
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
        while ("" === end($body)) {
            array_pop($body);
        }
        // Convert body to normal string again
        $body = implode("", array_slice(file($filename), $start_line, $length));

        $reflectionClass->body = $body;
        return $reflectionClass;
    }
}

/**
 * Reflect and clean code, to insert content easily and prevent whitespace problems
 */
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
        while ("" === end($body)) {
            array_pop($body);
        }
        // Convert body to normal string again
        $body = implode("", array_slice(file($filename), $start_line, $length));

        $reflection->body = $body;
        return $reflection;
    }
}

/**
 * Insert content starting from a defined line
 */
if (!function_exists('WriteContent')) {
    function WriteContent($content, $line, $addContent)
    {
        // Convert content to array
        $content = preg_split("/\\r\\n|\\r|\\n/", $content);
        $content = array_values($content);
        // Trim content whitespace
        while ("" === end($content)) {
            array_pop($content);
        }
        // Append content anywhere in this array
        $appendToRow = $line - 1;
        array_splice($content, $appendToRow, 0, $addContent);

        $content = implode("\n", $content);

        return $content;
    }
}

/**
 * Append content to a file, with additional offset
 */
if (!function_exists('AppendContent')) {
    function AppendContent($content, $offset, $addContent)
    {
        // Convert content to array
        $content = preg_split("/\\r\\n|\\r|\\n/", $content);
        $content = array_values($content);

        // Trim content whitespace
        while ("" === end($content)) {
            array_pop($content);
        }

        // Append content anywhere in this array
        $appendToRow = count($content) - $offset;
        array_splice($content, $appendToRow, 0, $addContent);

        $content = implode("\n", $content);

        return $content;
    }
}

/**
 * Grab a single rule from a Request
 */
if (!function_exists('GetRule')) {
    function GetRule($field, $ruleToGrab)
    {
        // Convert rule to array by exploding |, or simply looping if its an array
        $explodedRule = null;
        $fieldRules = (is_string($field)) ? explode('|', $field) : $field;
        foreach ($fieldRules as $key => $rule) {
            if (preg_match('/'.$ruleToGrab.'/',$rule)) {
                $explodedRule = explode(':', $rule)[1] ?? explode(':', $rule)[0];
                return $explodedRule;
            }
        }
    }
}

/**
 * Join eager loaded columns in query
 */
if (!function_exists('JoinRelations')) {
    function JoinRelations($query)
    {
        $relations = $query->getEagerLoads();
        $query->setEagerLoads([]);
        if (count($relations) > 0) {
            foreach ($relations as $relationName => $relationProps) {
                $relation = $query->getRelation($relationName);

                $foreignKey = $relation->getForeignKeyName();
                $ownerKey = $relation->getOwnerKeyName();

                $relationClass = FindClass($relationName)['class'];
                $relationTable = (new $relationClass())->getTable();
                $joinTable = $relationTable;
                $joinForeignKey = $foreignKey;
                $joinRightKey = $relationTable . '.' . $ownerKey;

                $query->join($joinTable, $joinForeignKey, '=', $joinRightKey);
            }
        }
        return $query;
    }
}

/**
 * Select all fields present in query
 */
if (!function_exists('SelectFields')) {
    function SelectFields($query, $table, $excludeFields = [])
    {
        $fields = Schema::getColumnListing($table);
        foreach ($fields as $key => $field) {
            if (!in_array($field, $excludeFields)) {
                $query->addSelect($table . '.' . $field);
            }
        }
        return $query;
    }
}

/**
 * Return user defined content if present in database
 */
if (!function_exists('DCMSContent')) {
    function DCMSContent($UID)
    {
        $content = Content::where('UID', $UID)->get()->first()->value ?? null;
        if ($content !== '') {
            return $content;
        }
        return null;
    }
}

/**
 * Return a string where for example: __name__ gets replaced with the objects' name attribute
 */
if (!function_exists('ReplaceWithAttr')) {
    function ReplaceWithAttr($message, $object)
    {
        preg_match_all('/__\S*__/m', $message, $matches);
        foreach ($matches[0] as $match) {
            $prop = str_replace('__', '', $match);
            $message = str_replace($match, $object->$prop, $message);
        }
        return $message;
    }
}
