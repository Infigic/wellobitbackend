<?php

use App\Http\Controllers\API\V2\CmsPageController as V2CmsPageController;
use App\Http\Controllers\API\V2\HrvController as V2HrvController;
use App\Http\Controllers\API\V2\RegisterController as V2RegisterController;
use App\Http\Controllers\API\V2\UserController as V2UserController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and assigned to the "api"
| middleware group. They are typically stateless and return JSON.
|--------------------------------------------------------------------------
*/

// Auth & Registration
Route::controller(V2RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api2.register');
    Route::post('verify-otp', 'verifyOtp')->name('api2.verifyOtp');
    Route::post('resend-otp', 'resendOtp')->name('api2.resendOtp');
    Route::post('login', 'login')->name('api2.login');
    Route::post('forget-password', 'forgetPassword')->name('api2.forgetPassword');
    Route::post('verify-reset-otp', 'verifyResetOtp')->name('api2.verifyResetOtp');
    Route::post('reset-password', 'resetPassword')->name('api2.resetPassword');
    Route::get('send-testmail', 'sendTestMail')->name('api2.sendTestMail');
});

// Public CMS Page
Route::post('pagedetail', [V2CmsPageController::class, 'getPageDetail'])->name('api2.cms.pagedetail');
Route::get('pages', [V2CmsPageController::class, 'getPages'])->name('api2.cms.pages');
// Protected Routes (Authenticated via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('logout', [V2RegisterController::class, 'logout'])->name('api2.logout');

    // Get Authenticated User Info
    Route::get('user', [V2UserController::class, 'getUserDetail'])->name('api2.user');
    Route::post('user', [V2UserController::class, 'updateUser'])->name('api2.updateuser');

    // HRV Logs
    Route::get('hrvs', [V2HrvController::class, 'index'])->name('api2.hrvs.index');
    Route::post('hrvs', [V2HrvController::class, 'store'])->name('api2.hrvs.store');

    Route::get('home', [V2HrvController::class, 'home'])->name('api2.hrvs.home');
});


