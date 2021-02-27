<?php

use Illuminate\Support\Facades\Route;

/**
* File uploads
*/

Route::group(['middleware' => ['web','auth','hasPermission']], function () {
    Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@ProcessFile')->name('dcms.filepond.process');
    Route::delete('/dcms/file/revert/{prefix}/{type}/{column}', 'Pveltrop\DCMS\Http\Controllers\FilepondController@DeleteFile')->name('dcms.filepond.delete');

    include __DIR__ . '/app/Routes/editor.php';
    include __DIR__ . '/app/Routes/portal.php';
});
