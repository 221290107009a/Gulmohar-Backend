<?php

use Illuminate\Support\Facades\Route;

Route::get('sellers', [
    'as' => 'admin.sellers.index',
    'uses' => 'SellerController@index',
    'middleware' => 'can:admin.sellers.index',
]);

Route::get('sellers/create', [
    'as' => 'admin.sellers.create',
    'uses' => 'SellerController@create',
    'middleware' => 'can:admin.sellers.create',
]);

Route::post('sellers', [
    'as' => 'admin.sellers.store',
    'uses' => 'SellerController@store',
    'middleware' => 'can:admin.sellers.create',
]);

Route::get('sellers/{id}/edit', [
    'as' => 'admin.sellers.edit',
    'uses' => 'SellerController@edit',
    'middleware' => 'can:admin.sellers.edit',
]);

Route::put('sellers/{id}/edit', [
    'as' => 'admin.sellers.update',
    'uses' => 'SellerController@update',
    'middleware' => 'can:admin.sellers.edit',
]);

Route::delete('sellers/{ids?}', [
    'as' => 'admin.sellers.destroy',
    'uses' => 'SellerController@destroy',
    'middleware' => 'can:admin.sellers.destroy',
]);

Route::get('sellers/index/table', [
    'as' => 'admin.sellers.table',
    'uses' => 'SellerController@table',
    'middleware' => 'can:admin.sellers.index',
]);
