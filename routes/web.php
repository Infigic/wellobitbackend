<?php

use App\Http\Controllers\CmsPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HrvController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FaqController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Auth routes with disabled register/reset
Auth::routes(['register' => false, 'reset' => false]);
Route::get('phpinfo', function () {
    phpinfo();
});
Route::get('testtoken/{id}', function ($id) {
    $user = App\Models\User::find($id);
    $token = $user->createToken('Aayoo')->plainTextToken;
    echo $token;
});
// CMS public pages
Route::get('page/{page}', [CmsPageController::class, 'getPageContent'])->name('cms.public');

// Routes that require authentication
Route::middleware(['auth'])->group(function () {

    // Home dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // User profile actions
    Route::get('users/editprofile', [UserController::class, 'EditProfile'])->name('users.editprofile');
    Route::get('users/togglestatus/{user}', [UserController::class, 'toggleStatus'])->name('users.togglestatus');
    Route::put('users/updateprofile', [UserController::class, 'UpdateProfile'])->name('users.updateprofile');
    Route::put('users/updatepassword', [UserController::class, 'UpdatePassword'])->name('users.updatepassword');

    // Force delete a user
    Route::delete('users/{user}/forcedelete', [UserController::class, 'forceDelete'])->name('users.forceDelete');

    // Resources
    Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
    Route::resource('quotes', QuoteController::class);
    Route::get('quotes/togglestatus/{quote}', [QuoteController::class, 'toggleStatus'])->name('quotes.togglestatus');
    Route::resource('cms-pages', CmsPageController::class);

    // HRV Routes
    Route::get('hrvs', [HrvController::class, 'index'])->name('hrvs.index');

    // Route for FAQs
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index'])->name('faqs.index');
        Route::get('/create', [FaqController::class, 'create'])->name('faqs.create');
        Route::get('/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');
        Route::post('/', [FaqController::class, 'store'])->name('faqs.store');  
    });
    
});

