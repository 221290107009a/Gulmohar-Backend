<?php

use Illuminate\Support\Facades\Route;

Route::get('blocks', [
    'as' => 'admin.blocks.index',
    'uses' => 'BlockController@index',
    'middleware' => 'can:admin.blocks.index',
]);

Route::get('blocks/create', [
    'as' => 'admin.blocks.create',
    'uses' => 'BlockController@create',
    'middleware' => 'can:admin.blocks.create',
]);

Route::post('blocks', [
    'as' => 'admin.blocks.store',
    'uses' => 'BlockController@store',
    'middleware' => 'can:admin.blocks.create',
]);

Route::get('blocks/{id}/edit', [
    'as' => 'admin.blocks.edit',
    'uses' => 'BlockController@edit',
    'middleware' => 'can:admin.blocks.edit',
]);

Route::put('blocks/{id}/edit', [
    'as' => 'admin.blocks.update',
    'uses' => 'BlockController@update',
    'middleware' => 'can:admin.blocks.edit',
]);

Route::delete('blocks/{ids?}', [
    'as' => 'admin.blocks.destroy',
    'uses' => 'BlockController@destroy',
    'middleware' => 'can:admin.blocks.destroy',
]);

Route::get('blocks/index/table', [
    'as' => 'admin.blocks.table',
    'uses' => 'BlockController@table',
    'middleware' => 'can:admin.blocks.index',
]);
