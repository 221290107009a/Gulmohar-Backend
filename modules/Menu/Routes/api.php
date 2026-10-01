<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Admin\MenuItemController;

Route::middleware('api_key')->get('menus', [MenuItemController::class, 'apiMenuItem']);