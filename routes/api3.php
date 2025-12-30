<?php

use App\Http\Controllers\API\V3\CmsPageController as V3CmsPageController;
use App\Http\Controllers\API\V3\HrvController as V3HrvController;
use App\Http\Controllers\API\V3\RegisterController as V3RegisterController;
use App\Http\Controllers\API\V3\UserController as V3UserController;
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
Route::controller(V3RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api3.register');
    Route::post('verify-otp', 'verifyOtp')->name('api3.verifyOtp');
    Route::post('resend-otp', 'resendOtp')->name('api3.resendOtp');
    Route::post('login', 'login')->name('api3.login');
    Route::post('forget-password', 'forgetPassword')->name('api3.forgetPassword');
    Route::post('verify-reset-otp', 'verifyResetOtp')->name('api3.verifyResetOtp');
    Route::post('reset-password', 'resetPassword')->name('api3.resetPassword');
});

// Public CMS Page
Route::post('pagedetail', [V3CmsPageController::class, 'getPageDetail'])->name('api3.cms.pagedetail');
Route::get('pages', [V3CmsPageController::class, 'getPages'])->name('api3.cms.pages');
// Protected Routes (Authenticated via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('logout', [V3RegisterController::class, 'logout'])->name('api3.logout');

    // Get Authenticated User Info
    Route::get('user', [V3UserController::class, 'getUserDetail'])->name('api3.user');
    Route::post('user', [V3UserController::class, 'updateUser'])->name('api3.updateuser');

    // HRV Logs
    Route::get('hrvs', [V3HrvController::class, 'index'])->name('api3.hrvs.index');
    Route::post('hrvs', [V3HrvController::class, 'store'])->name('api3.hrvs.store');

    Route::get('home', [V3HrvController::class, 'home'])->name('api3.hrvs.home');
});
