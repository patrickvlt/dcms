<?php

use Illuminate\Support\Facades\Route;

/**
* DCMS Dashboard
*/

Route::get('/dcms/dashboard', 'App\Http\Controllers\DCMSDashboardController@index')->name('dcms.portal.dashboard');

/**
* File uploads
*/

Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@ProcessFile');
Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'App\Http\Controllers\DCMSFilepondController@DeleteFile');

/**
* Use these routes as a reference for the DCMS editor
* Make sure your controller uses the DCMSContent trait
*/

// Route::post('/dcms/content/authenticate', 'App\Http\Controllers\ContentController@authenticate');
// Route::post('/dcms/content/update', 'App\Http\Controllers\ContentController@update');
// Route::post('/dcms/content/clear', 'App\Http\Controllers\ContentController@clear');

