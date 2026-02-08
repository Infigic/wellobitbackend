<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HrvController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CmsPageController;
use App\Http\Controllers\API\HrController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ReadinessScoreController;
use App\Http\Controllers\API\MindfulnessReportController;
use App\Http\Controllers\API\FaqCategoryController;
use App\Http\Controllers\API\FaqController;
use App\Http\Controllers\API\EventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and assigned to the "api"
| middleware group. They are typically stateless and return JSON.
|--------------------------------------------------------------------------
*/

// Auth & Registration
Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register')->name('api.register');
    Route::post('verify-otp', 'verifyOtp')->name('api.verifyOtp');
    Route::post('resend-otp', 'resendOtp')->name('api.resendOtp');
    Route::post('login', 'login')->name('api.login');
    Route::post('forget-password', 'forgetPassword')->name('api.forgetPassword');
    Route::post('verify-reset-otp', 'verifyResetOtp')->name('api.verifyResetOtp');
    Route::post('reset-password', 'resetPassword')->name('api.resetPassword');
});

// Public CMS Page
Route::post('pagedetail', [CmsPageController::class, 'getPageDetail'])->name('api.cms.pagedetail');
Route::get('pages', [CmsPageController::class, 'getPages'])->name('api.cms.pages');
// Protected Routes (Authenticated via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('logout', [RegisterController::class, 'logout'])->name('api.logout');

    // Get Authenticated User Info
    Route::get('user', [UserController::class, 'getUserDetail'])->name('api.user');
    Route::post('user', [UserController::class, 'updateUser'])->name('api.updateuser');

    //Store User Details
    Route::post('storeuserdetail', [UserController::class, 'storeUserDetails'])->name('api.storeUserDetails');

    // HRV Logs
    Route::get('hrvs', [HrvController::class, 'index'])->name('api.hrvs.index');
    Route::post('hrvs', [HrvController::class, 'store'])->name('api.hrvs.store');

    Route::get('home', [HrvController::class, 'home'])->name('api.hrvs.home');

    Route::prefix('faq-categories')->group(function () {
        Route::post('/', [FaqCategoryController::class, 'store']);
        Route::get('/', [FaqCategoryController::class, 'index']);
        Route::put('{id}', [FaqCategoryController::class, 'update']);
        Route::delete('{id}', [FaqCategoryController::class, 'destroy']);
    });
});

Route::post('/mindfulness/reports', [MindfulnessReportController::class, 'storeReports']);
Route::post('/mindfulness/reports/fetch', [MindfulnessReportController::class, 'fetchReportsByTimestamp']);

// Route::post('hr', [HrController::class, 'store'])->name('api.hr.store');
// Route::post('hr/fetch', [HrController::class, 'fetchByTimestamp'])->name('api.hr.fetch');
// Route::post('readiness/calculate', [ReadinessScoreController::class, 'calculate'])->name('api.readiness.calculate');

// FAQ
Route::prefix('faqs')->group(function () {
    // Public APIs (no auth / no role check)
    Route::get('/', [FaqController::class, 'index']); // Get list
    Route::get('{id}', [FaqController::class, 'show'])->whereNumber('id'); // Get detail

    // Protected APIs (admin only)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [FaqController::class, 'store']); // Create
        Route::patch('{id}', [FaqController::class, 'update'])->whereNumber('id'); // Update
        Route::delete('{id}', [FaqController::class, 'destroy'])->whereNumber('id'); // Delete
    });
});

// Public Event Tracking
Route::post('/events/public', [EventController::class, 'store']);

// Private Event Tracking
Route::post('events', [EventController::class, 'recordUserEvent'])
    ->middleware('auth:sanctum');
