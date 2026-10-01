<?php

use Illuminate\Support\Facades\Route;

Route::get('service-categories', [
    'as' => 'admin.services.index',
    'uses' => 'ServiceController@index',
    'middleware' => 'can:admin.services.index',
]);

Route::get('service-categories/index/table', [
    'as' => 'admin.services.table',
    'uses' => 'ServiceController@table',
    'middleware' => 'can:admin.services.index',
]);

Route::get('service-categories/create', [
    'as' => 'admin.services.create',
    'uses' => 'ServiceController@create',
    'middleware' => 'can:admin.services.create',
]);

Route::post('service-categories', [
    'as' => 'admin.services.store',
    'uses' => 'ServiceController@store',
    'middleware' => 'can:admin.services.create',
]);

Route::get('service-categories/{id}/edit', [
    'as' => 'admin.services.edit',
    'uses' => 'ServiceController@edit',
    'middleware' => 'can:admin.services.edit',
]);

Route::put('service-categories/{id}', [
    'as' => 'admin.services.update',
    'uses' => 'ServiceController@update',
    'middleware' => 'can:admin.services.edit',
]);

Route::delete('service-categories/{ids?}', [
    'as' => 'admin.services.destroy',
    'uses' => 'ServiceController@destroy',
    'middleware' => 'can:admin.services.destroy',
]);

Route::get('service-categories/{id}/search-users', [
    'as' => 'admin.services.search_users',
    'uses' => 'ServiceController@searchUsers',
    'middleware' => 'can:admin.services.edit',
]);

Route::post('service-categories/{id}/assign-user', [
    'as' => 'admin.services.assign_user',
    'uses' => 'ServiceController@assignUser',
    'middleware' => 'can:admin.services.edit',
]);
