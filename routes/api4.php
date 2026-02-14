<?php

use App\Http\Controllers\API\V4\CmsPageController as V4CmsPageController;
use App\Http\Controllers\API\V4\HrvController as V4HrvController;
use App\Http\Controllers\API\V4\RegisterController as V4RegisterController;
use App\Http\Controllers\API\V4\UserController as V4UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\MindfulnessReportController;
use App\Http\Controllers\API\V4\EventController;
use App\Http\Controllers\Auth\SocialAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and assigned to the "api"
| middleware group. They are typically stateless and return JSON.
|--------------------------------------------------------------------------
*/

// Auth & Registration
Route::controller(V4RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api4.register');
    Route::post('verify-otp', 'verifyOtp')->name('api4.verifyOtp');
    Route::post('resend-otp', 'resendOtp')->name('api4.resendOtp');
    Route::post('login', 'login')->name('api4.login');
    Route::post('forget-password', 'forgetPassword')->name('api4.forgetPassword');
    Route::post('verify-reset-otp', 'verifyResetOtp')->name('api4.verifyResetOtp');
    Route::post('reset-password', 'resetPassword')->name('api4.resetPassword');
});

// Public CMS Page
Route::post('pagedetail', [V4CmsPageController::class, 'getPageDetail'])->name('api4.cms.pagedetail');
Route::get('pages', [V4CmsPageController::class, 'getPages'])->name('api4.cms.pages');
// Protected Routes (Authenticated via Sanctum)
// Route::middleware(['auth:sanctum', 'use.v4.db'])->group(function () {
//     // Logout
//     Route::post('logout', [V4RegisterController::class, 'logout'])->name('api4.logout');

//     // Get Authenticated User Info
//     Route::get('user', [V4UserController::class, 'getUserDetail'])->name('api4.user');
//     Route::post('user', [V4UserController::class, 'updateUser'])->name('api4.updateuser');

//     // HRV Logs
//     Route::get('hrvs', [V4HrvController::class, 'index'])->name('api4.hrvs.index');
//     Route::post('hrvs', [V4HrvController::class, 'store'])->name('api4.hrvs.store');

//     Route::get('home', [V4HrvController::class, 'home'])->name('api4.hrvs.home');
//     Route::post('user_baseline', [V4HrvController::class, 'user_baseline'])->name('api4.user_baseline');
// });

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('logout', [V4RegisterController::class, 'logout'])->name('api3.logout');

    // Get Authenticated User Info
    Route::get('user', [V4UserController::class, 'getUserDetail'])->name('api3.user');
    Route::post('user', [V4UserController::class, 'updateUser'])->name('api3.updateuser');

    // HRV Logs
    Route::get('hrvs', [V4HrvController::class, 'index'])->name('api3.hrvs.index');
    Route::post('hrvs', [V4HrvController::class, 'store'])->name('api3.hrvs.store');

    Route::get('home', [V4HrvController::class, 'home'])->name('api3.hrvs.home');
    Route::post('user_baseline', [V4HrvController::class, 'user_baseline'])->name('api4.user_baseline');

    Route::post('/mindfulness/reports', [MindfulnessReportController::class, 'storeReports']);
    Route::post('/mindfulness/reports/fetch', [MindfulnessReportController::class, 'fetchReportsByTimestamp']);
});

Route::get('testtoken/{id}', function ($id) {
    $user = App\Models\User::find($id);
    $token = $user->createToken('Aayoo')->plainTextToken;
    echo $token;
});

// Public Event Tracking
Route::post('/events/public', [EventController::class, 'store']);

// User Tracking
Route::post('events', [EventController::class, 'recordUserEvent'])
    ->middleware('auth:sanctum');
