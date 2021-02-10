<?php

use Illuminate\Support\Facades\Route;

/**
* File uploads
*/

Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@ProcessFile');
Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@DeleteFile');

include __DIR__ . '/app/Routes/editor.php';
include __DIR__ . '/app/Routes/portal.php';
