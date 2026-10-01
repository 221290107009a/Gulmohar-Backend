<?php

use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use Modules\User\Http\Controllers\AuthController;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\FeedbackController;

Route::post('api_register', [AuthController::class, 'apiPostRegister']);

/* Route::post('login', [AuthController::class, 'apiPostLogin']); */
/* Route::middleware('api_key')->post('login', [AuthController::class, 'apiPostLogin']); */

Route::post('api_login', [AuthController::class, 'apiPostLogin']);

Route::middleware(['api_key'])->group(function() {
    Route::post('send-email-otp', [AuthController::class, 'apiGenerateOtp']);
    Route::post('verify-email-otp', [AuthController::class, 'apiVerifyEmailOtp']);
    Route::post('register', [AuthController::class, 'apiRegister']);
    Route::post('save-user-details', [AuthController::class, 'apiSaveUserDetails']);
    Route::get('user-detail/{id}', [AuthController::class, 'apiUserDetails']);
    Route::post('user-profile-save', [AuthController::class, 'apiSaveUserProfiles']);
    Route::post('user-profile-delete', [AuthController::class, 'apiDeleteUserProfiles']);
    Route::post('save-token', [AuthController::class, 'apiUserSaveToken']);
    Route::post('save-fcm-token', [AuthController::class, 'apiUserStoreFcmToken']);
    Route::post('save-device-info', [AuthController::class, 'apiUserSaveDeviceInfo']);
    Route::post('user-subscribe', [AuthController::class, 'apiUserSubscribe']);
    Route::get('active-subscription/{id}', [AuthController::class, 'apiActiveSubscription']);
    Route::get('referrer-total/{referrerId}', [AuthController::class, 'getTotalReferralsByReferrerId']);
    Route::get('reffered-users-list/{referrerId}', [AuthController::class, 'getRefferedUsersList']);
    Route::post('add-mobile-number', [AuthController::class, 'apiAddMobileNumber']);
    Route::post('user-referral-info', [AuthController::class, 'apiGetUserReferralInfo']);
    
    Route::post('app-share-messages', [AuthController::class, 'apiAppShareMessages']);
    
    Route::post('save-feedback', [FeedbackController::class, 'store']);
    
    // Location API endpoints
    Route::get('states', [AuthController::class, 'apiGetStates']);
    Route::get('districts-by-state', [AuthController::class, 'apiGetDistrictsByState']);
    Route::get('talukas-by-district', [AuthController::class, 'apiGetTalukasByDistrict']);
    Route::get('villages-by-taluka', [AuthController::class, 'apiGetVillagesByTaluka']);
    Route::post('pincode-info', [AuthController::class, 'apiGetPincodeInfo']);
    Route::post('upload-location-data', [AuthController::class, 'uploadLocationData']);
    Route::post('upload-pincode-location-data', [AuthController::class, 'uploadPincodeLocationData']);
});
