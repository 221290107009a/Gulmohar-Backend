<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

Route::get('orders/{order}/print', [OrderController::class, 'printOrderReceipt']);
Route::middleware(['api_key'])->group(function() {        
    
});