<?php

use Illuminate\Support\Facades\Route;

Route::get('management-country', 'CountryController@list')->name('country.list');