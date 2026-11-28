<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // defines the rules for a rate limiter named 'api'
        RateLimiter::for('api', function(Request $request) {
            return Limit::perMinute(30)
                ->by($request->user() ? $request->user()->id : $request->ip());
                // count separately per user if he/she logged in, else count per IP. So a user calling the API doesn't eat 
                // into another user's limit..
        });
    }
}
