<?php

use Illuminate\Support\Facades\Route;

Route::get('emailtemplates', [
    'as' => 'admin.emailtemplates.index',
    'uses' => 'EmailTemplateController@index',
    'middleware' => 'can:admin.emailtemplates.index',
]);

Route::get('emailtemplates/index/table', [
    'as' => 'admin.emailtemplates.table',
    'uses' => 'EmailTemplateController@table',
    'middleware' => 'can:admin.emailtemplates.index',
]);

Route::get('emailtemplates/create', [
    'as' => 'admin.emailtemplates.create',
    'uses' => 'EmailTemplateController@create',
    'middleware' => 'can:admin.emailtemplates.create',
]);

Route::post('emailtemplates', [
    'as' => 'admin.emailtemplates.store',
    'uses' => 'EmailTemplateController@store',
    'middleware' => 'can:admin.emailtemplates.create',
]);

Route::get('emailtemplates/{id}/edit', [
    'as' => 'admin.emailtemplates.edit',
    'uses' => 'EmailTemplateController@edit',
    'middleware' => 'can:admin.emailtemplates.edit',
]);

Route::put('emailtemplates/{id}/edit', [
    'as' => 'admin.emailtemplates.update',
    'uses' => 'EmailTemplateController@update',
    'middleware' => 'can:admin.emailtemplates.edit',
]);

Route::delete('emailtemplates/{ids?}', [
    'as' => 'admin.emailtemplates.destroy',
    'uses' => 'EmailTemplateController@destroy',
    'middleware' => 'can:admin.emailtemplates.destroy',
]);

Route::get('emailtemplates/duplicate/{id}', [
    'as' => 'admin.emailtemplates.duplicate',
    'uses' => 'EmailTemplateController@duplicate',
    'middleware' => 'can:admin.emailtemplates.create',
]);


Route::post('save-source', 'EmailTemplateController@saveSourceData')->name('save-source');

Route::post('save-preview', 'EmailTemplateController@PreviewData')->name('save-preview');

Route::post('send-email-manually', 'EmailTemplateController@sendEmailManually')->name('send-email-manually');
Route::post('send-notification-manually', 'EmailTemplateController@sendNotificationManually')->name('send-notification-manually');