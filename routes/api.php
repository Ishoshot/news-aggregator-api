<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/* -------------------------- Authentication Routes ------------------------- */

Route::middleware('throttle:12,1')->prefix('auth')->group(function (): void {

    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register');

    Route::middleware('throttle:login')->post('/login', [AuthenticatedUserController::class, 'store'])->name('auth.login');

    Route::post('/password/forgot', [PasswordResetLinkController::class, 'store'])->name('password.forgot');

    Route::post('/password/reset', [NewPasswordController::class, 'store'])->name('password.reset');

});
