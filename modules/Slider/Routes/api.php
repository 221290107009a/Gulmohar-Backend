<?php

use Illuminate\Support\Facades\Route;
use Modules\Slider\Http\Controllers\Admin\SliderController;

Route::middleware('api_key')->get('slider', [SliderController::class, 'sliderItem']);

Route::middleware('api_key')->get('ad-banner-slider', [SliderController::class, 'adBannerSlider']);

Route::middleware('api_key')->get('store-slider', [SliderController::class, 'storeSlider']);