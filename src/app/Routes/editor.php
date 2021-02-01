<?php

/**
* Make sure your controller uses the DCMSContent trait
*/

Route::post('/dcms/content/authenticate', 'Pveltrop\DCMS\Http\Controllers\ContentController@authenticate');
Route::post('/dcms/content/update', 'Pveltrop\DCMS\Http\Controllers\ContentController@update');
Route::post('/dcms/content/clear', 'Pveltrop\DCMS\Http\Controllers\ContentController@clear');