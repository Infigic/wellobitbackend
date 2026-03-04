<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\UserController as V1UserController;

Route::middleware('auth:sanctum', 'throttle:10,1')->group(function () {
    Route::delete('account', [V1UserController::class, 'deleteUser'])->name('api1.account.delete');
});