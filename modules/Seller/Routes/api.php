<?php

use Illuminate\Support\Facades\Route;
use Modules\Seller\Http\Controllers\SellerController;


Route::middleware(['api_key'])->group(function() {    
    Route::post('sellers-registration', [SellerController::class, 'apiSellerRegistration']);     
    Route::get('seller-status/{user_id}', [SellerController::class, 'apiGetSellerStatus']);
    Route::get('seller/orders/{user_id}', [SellerController::class, 'apiGetSellerOrders']);
    Route::post('seller/order/status-update', [SellerController::class, 'apiUpdateOrderStatus']);
    Route::get('seller-details/{user_id}', [SellerController::class, 'apiGetSellerById']);
    Route::post('seller/{user_id}', [SellerController::class, 'apiUpdateSellerDetails']);
});
