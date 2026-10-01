<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Http\Controllers\CheckoutController;
use Modules\Checkout\Http\Controllers\CheckoutCompleteController;
use Modules\Checkout\Http\Controllers\PaymentCanceledController;

Route::middleware(['api_key'])->group(function() {        
    Route::post('order-place', [CheckoutController::class, 'apiStore']);
    
    /* Route::any('checkout/{orderId}/complete', [CheckoutCompleteController::class, 'store']);
    Route::get('checkout/complete', [CheckoutCompleteController::class, 'show']);
    Route::get('checkout/{orderId}/payment-canceled', [PaymentCanceledController::class, 'store']); */
});