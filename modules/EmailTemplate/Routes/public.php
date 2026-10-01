<?php
use Illuminate\Support\Facades\Route;


// Route::get('case-studies/{slug}', 'EmailTemplateDetailController@emailTemplateDetail')->name('emailtemplate.detail');

// Route::get('case-studies', 'EmailTemplateDetailController@emailTemplateList')->name('emailtemplate.list');

Route::get('/', 'EmailTemplateController@index')->name('home');

Route::get('address-email-send-cron', 'EmailTemplateController@addressEmailSendCron')->name('address-email-send-cron');

Route::get('address-notification-send-cron', 'EmailTemplateController@addressNotificationSendCron')->name('address-notification-send-cron');