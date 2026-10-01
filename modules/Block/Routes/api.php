<?php

use Illuminate\Support\Facades\Route;
use Modules\Block\Http\Controllers\BlockController;

Route::middleware('api_key')->get('blocks', [BlockController::class,'apiAllBlocks']);
