<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\External\NewYorkTimes;
use Illuminate\Support\ServiceProvider;

final class NewYorkTimesServiceProvider extends ServiceProvider
{
    /**
     * Register the GitHub service.
     */
    public function register(): void
    {
        $this->app->singleton(NewYorkTimes::class, fn (): NewYorkTimes => new NewYorkTimes(config()->string('services.nytimes.base_url'), config()->string('services.nytimes.api_key')));
    }
}
