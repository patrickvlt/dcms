<?php

// Upload
Route::post('/file/process/{type}', 'App\Http\Controllers\FileController@ProcessFile');
Route::delete('/file/delete/{type}', 'App\Http\Controllers\FileController@DeleteFile');