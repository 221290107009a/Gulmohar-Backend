<?php

use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountOrdersController;
use Modules\Account\Http\Controllers\AccountWishlistProductController;

Route::middleware('api_key')->group(function () {
    /* Route::get('account', 'AccountDashboardController@index')->name('account.dashboard.index');
    Route::get('account/profile', 'AccountProfileController@edit')->name('account.profile.edit');
    Route::put('account/profile', 'AccountProfileController@update')->name('account.profile.update'); */

    Route::post('orders', [AccountOrdersController::class, 'apiIndex']);
    Route::get('orders/{id}', [AccountOrdersController::class, 'apiShow']);

    // Route::get('account/downloads', [AccountDownloadsController::class, 'apiIndex']);
    // Route::get('account/downloads/{id}', [AccountDownloadsController::class, 'apiShow']);
    
    Route::get('wishlist/{uid}', [AccountWishlistProductController::class, 'apiIndex']);
    Route::post('save-wishlist', [AccountWishlistProductController::class, 'apiStore']);
    Route::post('delete-wishlist', [AccountWishlistProductController::class, 'apiDestroy']);
    
    /* Route::get('account/wishlist', 'AccountWishlistController@index')->name('account.wishlist.index');

    Route::get('account/reviews', 'AccountReviewController@index')->name('account.reviews.index');

    Route::get('account/addresses', 'AccountAddressController@index')->name('account.addresses.index');
    Route::post('account/addresses', 'AccountAddressController@store')->name('account.addresses.store');
    Route::put('account/addresses/{id}', 'AccountAddressController@update')->name('account.addresses.update');
    Route::delete('account/addresses/{id}', 'AccountAddressController@destroy')->name('account.addresses.destroy');
    Route::post('account/addresses/change-default', 'AccountAddressController@changeDefault')->name('account.addresses.change_default'); */
});


