<?php

use Illuminate\Support\Facades\Route;

Route::get('products/{productId}/reviews', '\Modules\Review\Http\Controllers\ProductReviewController@index')->name('api.products.reviews.index');
Route::post('products/{productId}/reviews', '\Modules\Review\Http\Controllers\ProductReviewController@store')->name('api.products.reviews.store');
