<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\SuggestionController;

Route::middleware(['api_key'])->group(function() {    
    Route::get('online-store-messages', [ProductController::class, 'apiOnlineStoreMessages']);
    Route::get('products', [ProductController::class, 'apiProducts']);
    Route::get('online-store-products', [ProductController::class, 'apiOnlineStoreProducts']);
    Route::get('products/{slug}', [ProductController::class, 'apiProductShow']);
    Route::get('seller/products/{id}', [ProductController::class, 'productShowApi']);
    Route::post('seller/products/store', [ProductController::class, 'sellerProductStoreApi']);     
    Route::post('seller/products/update', [ProductController::class, 'sellerProductUpdateApi']);     
    Route::get('seller/{userId}/products', [ProductController::class, 'sellerProductsIndexApi']);
    Route::get('seller/products/remove/{id}', [ProductController::class, 'sellersProductsRemoveApi']);
});