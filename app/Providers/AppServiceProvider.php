<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
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
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Public endpoints that trigger (paid) model calls. Per-minute limit is generous
     * because a whole venue can share one IP; the daily caps bound the OpenAI bill.
     */
    protected function configureRateLimiting(): void
    {
        // Per-IP limits are generous: a whole venue (judges, demo audience) can share one Wi-Fi IP.
        // The global daily cap is what bounds the OpenAI bill.
        RateLimiter::for('ai', fn (Request $request) => [
            Limit::perMinute(60)->by('ai-minute|'.$request->ip()),
            Limit::perDay(1000)->by('ai-day|'.$request->ip()),
            Limit::perDay(3000)->by('ai-day-global'),
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
