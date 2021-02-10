<?php

if (config('dcms.portal') === true) {
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
        Route::resource('permission', 'Pveltrop\DCMS\Http\Controllers\PermissionController', ['as' => 'dcms.portal']);
        Route::resource('role', 'Pveltrop\DCMS\Http\Controllers\RoleController', ['as' => 'dcms.portal']);
        Route::resource('user', 'Pveltrop\DCMS\Http\Controllers\UserController', ['as' => 'dcms.portal']);
        Route::resource('model', 'Pveltrop\DCMS\Http\Controllers\ModelController', ['as' => 'dcms.portal']);
    });
}
