<?php

use Illuminate\Support\Facades\Route;

Route::get('countries', [
    'as' => 'admin.countries.index',
    'uses' => 'CountryController@index',
    'middleware' => 'can:admin.countries.index',
]);

Route::get('countries/create', [
    'as' => 'admin.countries.create',
    'uses' => 'CountryController@create',
    'middleware' => 'can:admin.countries.create',
]);

Route::post('countries', [
    'as' => 'admin.countries.store',
    'uses' => 'CountryController@store',
    'middleware' => 'can:admin.countries.create',
]);

Route::get('countries/{id}/edit', [
    'as' => 'admin.countries.edit',
    'uses' => 'CountryController@edit',
    'middleware' => 'can:admin.countries.edit',
]);

Route::put('countries/{id}/edit', [
    'as' => 'admin.countries.update',
    'uses' => 'CountryController@update',
    'middleware' => 'can:admin.countries.edit',
]);

Route::delete('countries/{ids?}', [
    'as' => 'admin.countries.destroy',
    'uses' => 'CountryController@destroy',
    'middleware' => 'can:admin.countries.destroy',
]);

Route::get('countries/index/table', [
    'as' => 'admin.countries.table',
    'uses' => 'CountryController@table',
    'middleware' => 'can:admin.countries.index',
]);
