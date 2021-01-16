<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Folders
    |--------------------------------------------------------------------------
    |
    | Define the folders which contain the Models you work with.
    | This will prevent just the app folder being scanned.
    |
    */

    'modelFolders' => ['app/Models'],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Configure storage service, globally or per model.
    | When changing storage service for a specific model, define an additional
    | array entry: ['storage']['service'][(route prefix/name of model)]
    |
    | available storage services: laravel & dropbox
    |
    */

    'storage' => [
        'service' => [
            'global' => 'laravel'
            // 'post' => 'dropbox',
        ]
    ]
];
