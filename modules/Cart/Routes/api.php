<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\CartItemController;

Route::middleware(['api_key'])->group(function() {    
    Route::get('user-address/{userid}', [CartController::class, 'apiGetUserAddress']);
    Route::post('save-user-address', [CartController::class, 'apiSaveUserAddress']);
    Route::post('update-user-address', [CartController::class, 'apiUpdateUserAddress']);
    Route::get('delete-user-address/{address_id}', [CartController::class, 'apiDeleteUserAddress']);
    
    Route::get('cart', [CartController::class, 'apiCartIndex']);
    Route::get('cart/clear', [CartController::class, 'apiClear']);

    Route::post('cart/items', [CartItemController::class, 'apiStore']);
    Route::post('cart/items/update/{id}', [CartItemController::class, 'apiUpdate']);
    Route::get('cart/items/delete/{id}', [CartItemController::class, 'apiDestroy']);       
    Route::put('cart/items/{id}', [CartItemController::class, 'apiUpdate']);
    Route::delete('cart/items/{id}', [CartItemController::class, 'apiDestroy']);
});