<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\External\TheGuardian;
use Illuminate\Support\ServiceProvider;

final class TheGuardianServiceProvider extends ServiceProvider
{
    /**
     * Register the GitHub service.
     */
    public function register(): void
    {
        $this->app->singleton(TheGuardian::class, fn (): TheGuardian => new TheGuardian(config()->string('services.theguardian.base_url'), config()->string('services.theguardian.api_key')));
    }
}
