<?php

use Illuminate\Support\Facades\Route;
use Modules\MembershipFee\Http\Controllers\Admin\MembershipFeeController;

Route::middleware(['api_key'])->group(function() {
    Route::get('subscription-plans', [MembershipFeeController::class, 'apiSubscriptionPlans']);
});    
Route::post('confirm', [MembershipFeeController::class, 'apiConfirm']);

?>