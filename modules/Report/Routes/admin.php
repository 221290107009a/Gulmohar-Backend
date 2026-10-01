<?php

use Illuminate\Support\Facades\Route;

Route::get('reports', [
    'as' => 'admin.reports.index',
    'uses' => 'ReportController@index',
    'middleware' => 'can:admin.reports.index',
]);

Route::get('user-reports', [
    'as' => 'admin.user_reports.index',
    'uses' => 'UserReportController@index',
    'middleware' => 'can:admin.user_reports.index',
]);
