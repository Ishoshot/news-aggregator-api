<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/* -------------------------- Authentication Routes ------------------------- */

Route::middleware('throttle:12,1')->prefix('auth')->group(function (): void {

    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register');

    Route::middleware('throttle:login')->post('/login', [AuthenticatedUserController::class, 'store'])->name('auth.login');

});
