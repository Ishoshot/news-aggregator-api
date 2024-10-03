<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\External\NewsApi;
use Illuminate\Support\ServiceProvider;

final class NewsApiServiceProvider extends ServiceProvider
{
    /**
     * Register the GitHub service.
     */
    public function register(): void
    {
        $this->app->singleton(NewsApi::class, fn (): NewsApi => new NewsApi(config()->string('services.newsapi.base_url'), config()->string('services.newsapi.api_key')));
    }
}
