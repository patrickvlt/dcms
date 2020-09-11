<?php

use Illuminate\Support\Facades\Route;

Route::post('/dcms/file/process/{prefix}/{type}/{column}', 'DCMSFilepondController@ProcessFile');