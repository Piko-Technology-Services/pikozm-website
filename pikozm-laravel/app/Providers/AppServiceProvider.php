<?php

namespace App\Providers;

use App\Services\DpoPay;
use App\Services\LencoPay;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DpoPay::class, fn ($app) => new DpoPay($app['config']->get('dpo')));
        $this->app->singleton(LencoPay::class, fn ($app) => new LencoPay($app['config']->get('lenco')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public form submissions: 10 per minute per visitor.
        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }
}
