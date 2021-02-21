<?php

/**
* Dynamic Content routes. This enables users to edit Front-End content on your website.
*/

Route::post('/dcms/content/authenticate', 'Pveltrop\DCMS\Http\Controllers\ContentController@authenticate')->name('dcms.content.authenticate');
Route::post('/dcms/content/update', 'Pveltrop\DCMS\Http\Controllers\ContentController@update')->name('dcms.content.update');
Route::post('/dcms/content/clear', 'Pveltrop\DCMS\Http\Controllers\ContentController@clear')->name('dcms.content.clear');
