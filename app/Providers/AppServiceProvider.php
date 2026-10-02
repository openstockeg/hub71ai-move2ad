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
        // Post-hackathon limits: low traffic expected, so keep the OpenAI bill tightly bounded.
        // The global daily cap is the hard ceiling.
        RateLimiter::for('ai', fn (Request $request) => [
            Limit::perMinute(10)->by('ai-minute|'.$request->ip()),
            Limit::perDay(30)->by('ai-day|'.$request->ip()),
            Limit::perDay(150)->by('ai-day-global'),
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
