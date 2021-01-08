<?php

namespace Pveltrop\DCMS\Classes;

class Dropbox
{
    public static function getKey(){
        $token = isset(auth()->user()->dropbox_key) && !empty(auth()->user()->dropbox_key) ? decrypt(auth()->user()->dropbox_key) : '';
        if (empty($token)) {
            $token = (env('DROPBOX_KEY') !== null) && !empty(env('DROPBOX_KEY')) ? decrypt(env('DROPBOX_KEY')) : '';
        }
        return $token;
    }

    public static function createLink($remotePath)
    {
        $token = self::getKey();
        $url = 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'path' => $remotePath,
            'settings' => [
                'requested_visibility' => 'public',
                'access' => 'viewer'
            ]
            ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        
        try {
            return $result->url.'&raw=1';
        } catch (\Throwable $th) {
            dd($result);
        };
    }

    public static function upload($file,$remoteFolder)
    {
        $fp = fopen($file, 'rb');
        $size = filesize($file);
        
        $token = self::getKey();
        $url = 'https://content.dropboxapi.com/2/files/upload';

        // Generate random remote file name
        $remoteFile = $file->hashName();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.
            json_encode([
                "path"=> $remoteFolder . '/' . $remoteFile,
                "mode" => "add",
                "autorename" => true,
                "mute" => false
            ])
        ]);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        curl_close($ch);
        fclose($fp);
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;

        if($status == 200){
            // Create shareable link
            return self::createLink($remoteFolder . '/' . $remoteFile);
        }
        return $response;
    }

    public static function remove($remotePath)
    {
        $url = 'https://api.dropboxapi.com/2/files/delete_v2';
        $token = self::getKey();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
            
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['path' => $remotePath]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;
        return $response;
    }

    public static function move($oldPath,$newPath)
    {
        $token = self::getKey();
        $url = 'https://api.dropboxapi.com/2/files/move_v2';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'from_path' => $oldPath,
            'to_path' => $newPath,
            'autorename' => false,
            'allow_ownership_transfer' => false
        ]));
        
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;
        
        return $response;
    }

    public static function findBySharedLink($link)
    {
        $token = self::getKey();
        $url = 'https://api.dropboxapi.com/2/sharing/get_shared_link_metadata';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'url' => $link
        ]));
        
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;
        
        return $response;
    }

    public static function findByPath($path)
    {
        $token = self::getKey();
        $url = 'https://api.dropboxapi.com/2/files/get_metadata';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'path' => $path
        ]));
        
        $status = 200;
        $result = curl_exec($ch);
        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;
        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;
        
        return $response;
    }
}
