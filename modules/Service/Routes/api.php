<?php

use Illuminate\Support\Facades\Route;
use Modules\Service\Http\Controllers\ServiceController;
use Modules\Service\Http\Controllers\HomeScreenController;

Route::middleware(['api_key'])->group(function() {
    Route::post('services', [ServiceController::class, 'apiServices']);
    Route::post('home-screen', [HomeScreenController::class, 'index']); // V2 API (V1 removed)
    Route::post('home-screen/v2', [HomeScreenController::class, 'index']); // Alias for backwards compatibility
    Route::post('home-screen/daily-highlights', [HomeScreenController::class, 'getDailyHighlights']);
});    

?>