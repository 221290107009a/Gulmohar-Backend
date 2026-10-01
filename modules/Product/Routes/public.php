<?php

use Illuminate\Support\Facades\Route;

Route::get('products', 'ProductController@index')->name('products.index');

Route::get('products/{slug}', 'ProductController@show')->name('products.show');

Route::post('products/{id}/price', 'ProductPriceController@show')->name('products.price.show');

Route::get('suggestions', 'SuggestionController@index')->name('suggestions.index');

Route::get('store/product/{id}/ref/{referralCode}', 'ProductController@getProductShareUrl')->name('products.referral.link');

// New routes for renamed online-store paths
Route::get('online-store/product/{id}/ref/{referralCode}', 'ProductController@getProductShareUrl')->name('products.online_store.referral.link');
Route::get('online-store/bahujan-sahitya/product/{id}/ref/{referralCode}', 'ProductController@getBahujanProductShareUrl')->name('products.bahujan.referral.link');

