<?php

use Illuminate\Support\Facades\Route;
use FleetCart\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Artisan;

Route::get('install', 'InstallController@installation')->name('install.show');
Route::post('install', 'InstallController@install')->name('install.do');

Route::get('license', 'LicenseController@create')->name('license.create');
Route::post('license', 'LicenseController@store')->name('license.store');

Route::get('/google/auth', [GoogleAuthController::class, 'redirect']);
Route::get('/google/callback', [GoogleAuthController::class, 'callback']);

Route::get('/cron/google-drive-backup', function () {
    /* $token = request()->get('token');
    $expectedToken = env('API_KEY');

    if (empty($token) || $token !== $expectedToken) {
        return response()->json(['success' => false, 'message' => 'Unauthorized token.'], 403);
    } */

    try {
        @set_time_limit(0);
        Artisan::call('backup:google-drive');
        $output = Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Backup executed successfully.',
            'output' => trim($output)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Backup failed: ' . $e->getMessage()
        ], 500);
    }
});
