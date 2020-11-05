<?php

use Illuminate\Support\Facades\Route;

/**
* DCMS Dashboard
*/

Route::get('/dcms/dashboard', 'App\Http\Controllers\DCMSDashboardController@index');

/**
* File uploads
*/

Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@ProcessFile');
Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@DeleteFile');