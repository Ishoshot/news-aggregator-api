<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSQLitePerformance();
        $this->configureModels();
        $this->configureDates();
        $this->configurePasswordValidation();
        $this->configureRateLimits();
    }

    /**
     * Configure the models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn () => Password::min(8)->numbers()->symbols()->letters()->mixedCase());
    }

    /**
     * Configure the SQLite connection for performance optimization.
     */
    private function configureSQLitePerformance(): void
    {
        try {
            DB::connection('sqlite')->statement('PRAGMA synchronous = OFF;');
        } catch (Throwable $e) { // @codeCoverageIgnoreStart
            return;
        } // @codeCoverageIgnoreEnd
    }

    /**
     * Configure rate limits.
     */
    private function configureRateLimits(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {

            $email = type($request->input('email'))->asString();

            $throttleKey = Str::transliterate(Str::lower($email).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
