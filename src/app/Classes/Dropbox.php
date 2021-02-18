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

    public static function curlRequest($url,$headers=null,$postFields=null,$file=null){
        $ch = curl_init($url);

        if(!$headers){
            $token = self::getKey();
            $headers = [
                'Authorization: Bearer '.$token,
                'Content-Type: application/json',
            ];
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        if($file){
            $fp = fopen($file, 'rb');
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($file, 'rb'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        
        $result = curl_exec($ch);
        $status = 200;

        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = (json_decode($result) !== null) ? json_decode($result) : $result;

        curl_close($ch);
        if($file){
            fclose($fp);
        }

        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;

        return $response;
    }

    public static function createLink($remotePath)
    {
        $url = 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings';

        $curl = self::curlRequest($url,null,json_encode([
            'path' => $remotePath,
            'settings' => [
                'requested_visibility' => 'public',
                'access' => 'viewer'
            ]
        ]));
        
        return $curl->response->url.'&raw=1';
    }

    public static function upload($file,$remoteFolder)
    {
        $url = 'https://content.dropboxapi.com/2/files/upload';
        $remoteFile = $file->hashName();
        $headers = [
            'Authorization: Bearer '.self::getKey(),
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.
            json_encode([
                "path"=> $remoteFolder . '/' . $remoteFile,
                "mode" => "add",
                "autorename" => true,
                "mute" => false
            ])
        ];

        $curl = self::curlRequest($url,$headers,null,$file);
        if($curl->status == 200){
            // Create shareable link
            return self::createLink($remoteFolder . '/' . $remoteFile);
        }

        return $curl->response;
    }

    public static function remove($remotePath)
    {
        $url = 'https://api.dropboxapi.com/2/files/delete_v2';
        $postFields = json_encode(['path' => $remotePath]);
        $headers = [
            'Authorization: Bearer '.self::getKey(),
            'Content-Type: application/json',
        ];

        $curl = self::curlRequest($url,$headers,$postFields);
        
        return $curl->status;
    }

    public static function move($oldPath,$newPath)
    {
        $url = 'https://api.dropboxapi.com/2/files/move_v2';
        $postFields = json_encode([
            'from_path' => $oldPath,
            'to_path' => $newPath,
            'autorename' => false,
            'allow_ownership_transfer' => false
        ]);
        
        $curl = self::curlRequest($url,null,$postFields);
        
        return $curl;
    }

    public static function findBySharedLink($link)
    {
        $url = 'https://api.dropboxapi.com/2/sharing/get_shared_link_metadata';
        $postFields = json_encode([
            'url' => $link
        ]);

        $curl = self::curlRequest($url,null,$postFields);
        
        return $curl;
    }

    public static function findByPath($path)
    {
        $url = 'https://api.dropboxapi.com/2/files/get_metadata';
        $postFields = json_encode([
            'path' => $path
        ]);
        
        $curl = self::curlRequest($url,null,$postFields);
        
        return $curl;
    }
}
