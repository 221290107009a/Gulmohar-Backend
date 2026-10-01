<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Illuminate\Http\Request;

Route::middleware(['api_key'])->group(function() { 
    Route::get('categories', [CategoryController::class, 'getCategoryListApi']);
    Route::get('online-store-categories', [CategoryController::class, 'onlineStoreCategoryListApi']);
    Route::post('category/subcategories', [CategoryController::class, 'getSubcategoriesBySlug']);
    Route::get('parent-categories', [CategoryController::class, 'getParentCategoriesApi']);
    Route::get('category/subcategories/{id}', [CategoryController::class, 'getSubcategoriesById']);
});