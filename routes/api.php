<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/* -------------------------- Authentication Routes ------------------------- */

Route::prefix('auth')->group(function (): void {

    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register');
});
