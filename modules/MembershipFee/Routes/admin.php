<?php

use Illuminate\Support\Facades\Route;

Route::get('membership-fees', [
    'as' => 'admin.membership_fees.index',
    'uses' => 'MembershipFeeController@index',
    'middleware' => 'can:admin.membership_fees.index',
]);

Route::get('membership-fees/index/table', [
    'as' => 'admin.membership_fees.table',
    'uses' => 'MembershipFeeController@table',
    'middleware' => 'can:admin.membership_fees.index',
]);

Route::get('membership-fees/create', [
    'as' => 'admin.membership_fees.create',
    'uses' => 'MembershipFeeController@create',
    'middleware' => 'can:admin.membership_fees.create',
]);

Route::post('membership-fees', [
    'as' => 'admin.membership_fees.store',
    'uses' => 'MembershipFeeController@store',
    'middleware' => 'can:admin.membership_fees.create',
]);

Route::get('membership-fees/{id}/edit', [
    'as' => 'admin.membership_fees.edit',
    'uses' => 'MembershipFeeController@edit',
    'middleware' => 'can:admin.membership_fees.edit',
]);

Route::put('membership-fees/{id}', [
    'as' => 'admin.membership_fees.update',
    'uses' => 'MembershipFeeController@update',
    'middleware' => 'can:admin.membership_fees.edit',
]);

Route::delete('membership-fees/{ids?}', [
    'as' => 'admin.membership_fees.destroy',
    'uses' => 'MembershipFeeController@destroy',
    'middleware' => 'can:admin.membership_fees.destroy',
]);
