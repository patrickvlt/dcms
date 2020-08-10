<?php

// Upload
Route::post('/dcms/file/process/{type}', 'App\Http\Controllers\FileController@ProcessFile');
Route::delete('/dcms/file/delete/{type}', 'App\Http\Controllers\FileController@DeleteFile');