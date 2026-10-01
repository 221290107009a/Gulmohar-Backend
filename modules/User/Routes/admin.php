<?php

use Illuminate\Support\Facades\Route;

Route::get('login', 'AuthController@getLogin')->name('admin.login');
Route::post('login', 'AuthController@postLogin')->name('admin.login.post');

Route::get('logout', 'AuthController@getLogout')->name('admin.logout');

Route::get('password/reset', 'AuthController@getReset')->name('admin.reset');
Route::post('password/reset', 'AuthController@postReset')->name('admin.reset.post');
Route::get('password/reset/{email}/{code}', 'AuthController@getResetComplete')->name('admin.reset.complete');
Route::post('password/reset/{email}/{code}', 'AuthController@postResetComplete')->name('admin.reset.complete.post');

Route::get('users/search', [
    'as' => 'admin.users.search',
    'uses' => 'UserController@search',
    'middleware' => 'can:admin.users.index',
]);

Route::get('users/districts', [
    'as' => 'admin.users.districts',
    'uses' => 'UserController@getDistricts',
]);

Route::get('users/talukas', [
    'as' => 'admin.users.talukas',
    'uses' => 'UserController@getTalukas',
]);

Route::get('users/villages', [
    'as' => 'admin.users.villages',
    'uses' => 'UserController@getVillages',
]);

Route::get('users/pincode', [
    'as' => 'admin.users.pincode',
    'uses' => 'UserController@getPincode',
]);

Route::get('users', [
    'as' => 'admin.users.index',
    'uses' => 'UserController@index',
    'middleware' => 'can:admin.users.index',
]);

Route::get('users/create', [
    'as' => 'admin.users.create',
    'uses' => 'UserController@create',
    'middleware' => 'can:admin.users.create',
]);

Route::post('users', [
    'as' => 'admin.users.store',
    'uses' => 'UserController@store',
    'middleware' => 'can:admin.users.create',
]);

Route::get('users/{id}/edit', [
    'as' => 'admin.users.edit',
    'uses' => 'UserController@edit',
    'middleware' => 'can:admin.users.edit',
]);

Route::put('users/{id}/edit', [
    'as' => 'admin.users.update',
    'uses' => 'UserController@update',
    'middleware' => 'can:admin.users.edit',
]);

Route::delete('users/{ids?}', [
    'as' => 'admin.users.destroy',
    'uses' => 'UserController@destroy',
    'middleware' => 'can:admin.users.destroy',
]);

Route::get('users/index/table', [
    'as' => 'admin.users.table',
    'uses' => 'UserController@table',
    'middleware' => 'can:admin.users.index',
]);

Route::get('users/{id}/reset-password', [
    'as' => 'admin.users.reset_password',
    'uses' => 'UserResetPasswordController@store',
    'middleware' => 'can:admin.users.edit',
]);

Route::get('roles', [
    'as' => 'admin.roles.index',
    'uses' => 'RoleController@index',
    'middleware' => 'can:admin.roles.index',
]);

Route::get('roles/index/table', [
    'as' => 'admin.roles.table',
    'uses' => 'RoleController@table',
    'middleware' => 'can:admin.roles.index',
]);

Route::get('roles/create', [
    'as' => 'admin.roles.create',
    'uses' => 'RoleController@create',
    'middleware' => 'can:admin.roles.create',
]);

Route::post('roles', [
    'as' => 'admin.roles.store',
    'uses' => 'RoleController@store',
    'middleware' => 'can:admin.roles.create',
]);

Route::get('roles/{id}/edit', [
    'as' => 'admin.roles.edit',
    'uses' => 'RoleController@edit',
    'middleware' => 'can:admin.roles.edit',
]);

Route::put('roles/{id}/edit', [
    'as' => 'admin.roles.update',
    'uses' => 'RoleController@update',
    'middleware' => 'can:admin.roles.edit',
]);

Route::delete('roles/{ids?}', [
    'as' => 'admin.roles.destroy',
    'uses' => 'RoleController@destroy',
    'middleware' => 'can:admin.roles.destroy',
]);

// Profile
Route::get('profile', [
    'as' => 'admin.profile.edit',
    'uses' => 'ProfileController@edit',
]);

Route::put('profile', [
    'as' => 'admin.profile.update',
    'uses' => 'ProfileController@update',
]);

//member
Route::get('member', [
    'as' => 'admin.member.index',
    'uses' => 'MemberController@index',
]);

Route::get('member/create', [
    'as' => 'admin.member.create',
    'uses' => 'MemberController@create',
]);

Route::post('member', [
    'as' => 'admin.member.store',
    'uses' => 'MemberController@store',
]);
Route::get('member/{id}/edit', [
    'as' => 'admin.member.edit',
    'uses' => 'MemberController@edit',
]);

Route::put('member/{id}/edit', [
    'as' => 'admin.member.update',
    'uses' => 'MemberController@update',
]);

Route::delete('member/{ids?}', [
    'as' => 'admin.member.destroy',
    'uses' => 'MemberController@destroy',
]);

Route::get('member/{id}/reset-password', [
    'as' => 'admin.member.reset_password',
    'uses' => 'UserResetPasswordController@store',
]);

Route::get('icard-generate/{id?}', 'MemberController@generateICard')->name('icard-generate');

Route::get('member/download-all', [
    'as' => 'admin.member.download-all',
    'uses' => 'MemberController@downloadAll',
]);
Route::get('member/index/table', [
    'as' => 'admin.member.table',
    'uses' => 'MemberController@table'
]);
Route::post('member/{id}/services/{serviceId}/toggle', [
    'as' => 'admin.member.services.toggle',
    'uses' => 'UserController@toggleService'
]);

// Subscription History
Route::get('subscription-histories', [
    'as' => 'admin.subscription_histories.index',
    'uses' => 'SubscriptionHistoryController@index'
]);

Route::get('subscription-histories/index/table', [
    'as' => 'admin.subscription_histories.table',
    'uses' => 'SubscriptionHistoryController@table'
]);
Route::get('subscription-histories/{id}', [
    'as' => 'admin.subscription_histories.show',
    'uses' => 'SubscriptionHistoryController@show',
]);

// Reffered Users List
Route::get('reffered-users-list', [
    'as' => 'admin.reffered_users_list.index',
    'uses' => 'RefferedUsersListController@index'
]);

Route::get('reffered-users-list/index/table', [
    'as' => 'admin.reffered_users_list.table',
    'uses' => 'RefferedUsersListController@table'
]);
Route::get('reffered-users-list/{id}', [
    'as' => 'admin.reffered_users_list.show',
    'uses' => 'RefferedUsersListController@show',
]);


// Feedback
Route::get('feedback', [
    'as' => 'admin.feedback.index',
    'uses' => 'FeedbackController@index',
]);

Route::get('feedback/index/table', [
    'as' => 'admin.feedback.table',
    'uses' => 'FeedbackController@table',
]);

Route::get('feedback/{id}', [
    'as' => 'admin.feedback.show',
    'uses' => 'FeedbackController@show',
]);

Route::put('feedback/{id}', [
    'as' => 'admin.feedback.update',
    'uses' => 'FeedbackController@update',
]);

Route::delete('feedback/{id}', [
    'as' => 'admin.feedback.destroy',
    'uses' => 'FeedbackController@destroy',
]);
