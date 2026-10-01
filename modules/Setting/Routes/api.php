<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\Admin\SettingController;

/* Route::get('settings', [SettingController::class, 'allSettings']); */
Route::middleware('api_key')->get('settings', [SettingController::class, 'allSettings']);
