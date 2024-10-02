<?php

declare(strict_types=1);

use App\Http\Controllers\ArticleAuthorController;
use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleSourceController;
use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserArticlePreferenceController;
use Illuminate\Support\Facades\Route;

/* -------------------------- Authentication Routes ------------------------- */

Route::middleware('throttle:12,1')->prefix('auth')->group(function (): void {

    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register');

    Route::middleware('throttle:login')->post('/login', [AuthenticatedUserController::class, 'store'])->name('auth.login');

    Route::post('/password/forgot', [PasswordResetLinkController::class, 'store'])->name('password.forgot');

    Route::post('/password/reset', [NewPasswordController::class, 'store'])->name('password.reset');

});

/* -------------------------- User Routes Sources ------------------------- */

Route::middleware('auth:sanctum')->prefix('user')->group(function (): void {

    // List all article sources
    Route::get('/article-source', [ArticleSourceController::class, 'index'])->name('user.article-source.list');

    // List all article categories
    Route::get('/article-category', [ArticleCategoryController::class, 'index'])->name('user.article-category.list');

    // List all article authors
    Route::get('/article-author', [ArticleAuthorController::class, 'index'])->name('user.article-author.list');

    // Manage user preferences
    Route::prefix('preference')->group(function (): void {

        Route::get('/', [UserArticlePreferenceController::class, 'index'])->name('user.preference.list');

        Route::post('/', [UserArticlePreferenceController::class, 'store'])->name('user.preference.store');

    });

    // Article Management
    Route::prefix('article')->group(function (): void {

        Route::get('/', [ArticleController::class, 'index'])->name('user.article.list');

    });

});
