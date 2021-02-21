<?php

if (config('dcms.portal') === true) {
    Route::prefix('/dcms')->group(function () {
        /**
        * Portal navigation
        */

        Route::get('/dashboard', 'Pveltrop\DCMS\Http\Controllers\PortalController@index')->name('dcms.portal.dashboard');
        Route::get('/activity', 'Pveltrop\DCMS\Http\Controllers\PortalController@activity')->name('dcms.portal.activity');
        Route::get('/authorization', 'Pveltrop\DCMS\Http\Controllers\AuthorizationController@index')->name('dcms.portal.authorization.index');

        /**
         * User routes
         */

        Route::get('/fetch/user', 'Pveltrop\DCMS\Http\Controllers\UserController@fetch')->name('dcms.portal.user.fetch');
        Route::delete('/user/multiple', 'Pveltrop\DCMS\Http\Controllers\UserController@destroyMultiple')->name('dcms.portal.user.destroy.multiple');

        /**
         * Model routes
         */

        Route::get('/fetch/model', 'Pveltrop\DCMS\Http\Controllers\ModelController@fetch')->name('dcms.portal.model.fetch');
        Route::resource('model', 'Pveltrop\DCMS\Http\Controllers\ModelController', ['as' => 'dcms.portal']);

        /**
         * Roles and permissions
         */

        Route::get('/permission/fetch', 'Pveltrop\DCMS\Http\Controllers\PermissionController@fetch')->name('dcms.portal.permission.fetch');
        Route::delete('/permission/multiple', 'Pveltrop\DCMS\Http\Controllers\PermissionController@destroyMultiple')->name('dcms.portal.permission.destroy.multiple');
        Route::resource('permission', 'Pveltrop\DCMS\Http\Controllers\PermissionController', ['as' => 'dcms.portal']);

        Route::get('/role/fetch', 'Pveltrop\DCMS\Http\Controllers\RoleController@fetch')->name('dcms.portal.role.fetch');
        Route::delete('/role/multiple', 'Pveltrop\DCMS\Http\Controllers\RoleController@destroyMultiple')->name('dcms.portal.role.destroy.multiple');
        Route::resource('role', 'Pveltrop\DCMS\Http\Controllers\RoleController', ['as' => 'dcms.portal']);

        Route::resource('user', 'Pveltrop\DCMS\Http\Controllers\UserController', ['as' => 'dcms.portal']);
    });
}
