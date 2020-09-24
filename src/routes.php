<?php

use Illuminate\Support\Facades\Route;

Route::get('/dcms/generate', 'App\Http\Controllers\DCMSGenerateController@generate');
// Filepond
Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@ProcessFile');
Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@DeleteFile');