<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogPostController;

Route::middleware('api_key')->get('blog/posts', [BlogPostController::class, 'apiAllBlogs']);
Route::middleware('api_key')->get('blog/posts/{blogslug}', [BlogPostController::class, 'apiBlogsDetail']);