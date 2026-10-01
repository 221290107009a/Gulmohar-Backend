<?php

use Illuminate\Support\Facades\Route;
use Modules\Page\Http\Controllers\PageController;

Route::middleware("api_key")->get("/pages",[PageController::class, "apiAllPages"]);
Route::middleware("api_key")->get("/page/{pageslug}",[PageController::class, "apiPageDetailUsingSlug"]);
