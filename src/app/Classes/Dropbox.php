<?php

namespace Pveltrop\DCMS\Classes;

class Dropbox
{
    /**
     * Get the Dropbox API key from the .env and decrypt it
     * @return string
     */
    public static function getKey(): string
    {
        $token = isset(auth()->user()->dropbox_key) && !empty(auth()->user()->dropbox_key) ? decrypt(auth()->user()->dropbox_key) : '';
        if (empty($token)) {
            $token = (env('DROPBOX_KEY') !== null) && !empty(env('DROPBOX_KEY')) ? decrypt(env('DROPBOX_KEY')) : '';
        }
        return $token;
    }

    /**
     * Initialise and send the curl request to the Dropbox API
     * @param $url
     * @param null $headers
     * @param null $postFields
     * @param null $file
     * @return object
     */
    public static function curlRequest($url, $headers=null, $postFields=null, $file=null): object
    {
        $ch = curl_init($url);

        if (!$headers) {
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

        if ($file) {
            $fp = fopen($file, 'rb');
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($file, 'rb'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        $result = curl_exec($ch);

        // Check HTTP status code
        if (!curl_errno($ch)) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } else {
            $status = 500;
        }
        $result = json_decode($result, true) ?? $result;

        curl_close($ch);
        if ($file) {
            fclose($fp);
        }

        $response = (object) 'dropbox';
        $response->status = $status;
        $response->response = $result;

        return $response;
    }

    /**
     * Create a shareable link for the Dropbox file
     * @param $remotePath
     * @return string
     */
    public static function createLink($remotePath): string
    {
        $url = 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings';

        $curl = self::curlRequest($url, null, json_encode([
            'path' => $remotePath,
            'settings' => [
                'requested_visibility' => 'public',
                'access' => 'viewer'
            ]
        ]));

        return $curl->response->url.'&raw=1';
    }

    /**
     * Upload a file to Dropbox
     * @param $file
     * @param $remoteFolder
     * @return string
     */
    public static function upload($file, $remoteFolder): string
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

        $curl = self::curlRequest($url, $headers, null, $file);
        if ($curl->status === 200) {
            // Create shareable link
            return self::createLink($remoteFolder . '/' . $remoteFile);
        }

        return $curl->response;
    }

    /**
     * Remove a file from Dropbox
     * @param $remotePath
     * @return mixed
     */
    public static function remove($remotePath)
    {
        $url = 'https://api.dropboxapi.com/2/files/delete_v2';
        $postFields = json_encode(['path' => $remotePath]);
        $headers = [
            'Authorization: Bearer '.self::getKey(),
            'Content-Type: application/json',
        ];

        return self::curlRequest($url, $headers, $postFields)->status;
    }

    /**
     * Move a file to another destination
     * @param $oldPath
     * @param $newPath
     * @return object
     */
    public static function move($oldPath, $newPath): object
    {
        $url = 'https://api.dropboxapi.com/2/files/move_v2';
        $postFields = json_encode([
            'from_path' => $oldPath,
            'to_path' => $newPath,
            'autorename' => false,
            'allow_ownership_transfer' => false
        ]);

        return self::curlRequest($url, null, $postFields);
    }

    /**
     * Find a file by searching with a shared link
     * @param $link
     * @return object
     */
    public static function findBySharedLink($link): object
    {
        $url = 'https://api.dropboxapi.com/2/sharing/get_shared_link_metadata';
        $postFields = json_encode([
            'url' => $link
        ]);

        return self::curlRequest($url, null, $postFields);
    }

    /**
     * Find a file by searching with a path
     * @param $path
     * @return object
     */
    public static function findByPath($path): object
    {
        $url = 'https://api.dropboxapi.com/2/files/get_metadata';
        $postFields = json_encode([
            'path' => $path
        ]);

        return self::curlRequest($url, null, $postFields);
    }
}
