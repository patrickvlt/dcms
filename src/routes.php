<?php

use Illuminate\Support\Facades\Route;

/**
* DCMS Portal
*/

Route::get('/dcms/dashboard', 'Pveltrop\DCMS\Http\Controllers\PortalController@index')->name('dcms.portal.dashboard');
Route::get('/dcms/activity', 'Pveltrop\DCMS\Http\Controllers\PortalController@activity')->name('dcms.portal.activity');

/**
 * User routes
 */

Route::get('/dcms/fetch/user', 'Pveltrop\DCMS\Http\Controllers\UserController@fetch')->name('dcms.portal.user.fetch');
Route::delete('/dcms/user/multiple', 'Pveltrop\DCMS\Http\Controllers\UserController@destroyMultiple')->name('dcms.portal.user.destroy.multiple');

/**
 * Model routes
 */

Route::get('/dcms/fetch/model', 'Pveltrop\DCMS\Http\Controllers\ModelController@fetch')->name('dcms.portal.model.fetch');

Route::prefix('/dcms')->group(function () {
    Route::resource('permission', 'Pveltrop\DCMS\Http\Controllers\PermissionController',['as' => 'dcms.portal']);
    Route::resource('role', 'Pveltrop\DCMS\Http\Controllers\RoleController',['as' => 'dcms.portal']);
    Route::resource('user', 'Pveltrop\DCMS\Http\Controllers\UserController',['as' => 'dcms.portal']);
    Route::resource('model', 'Pveltrop\DCMS\Http\Controllers\ModelController',['as' => 'dcms.portal']);
});

/**
* File uploads
*/

Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@ProcessFile');
Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@DeleteFile');

/**
* Use these routes as a reference for the DCMS editor
* Make sure your controller uses the DCMSContent trait
*/

// Route::post('/dcms/content/authenticate', 'Pveltrop\DCMS\Http\Controllers\ContentController@authenticate');
// Route::post('/dcms/content/update', 'Pveltrop\DCMS\Http\Controllers\ContentController@update');
// Route::post('/dcms/content/clear', 'Pveltrop\DCMS\Http\Controllers\ContentController@clear');

