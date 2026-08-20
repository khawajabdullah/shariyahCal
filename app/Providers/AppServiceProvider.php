<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::useCspNonce();

        RateLimiter::for('admin-login', function (Request $request) {
            $email = (string) $request->input('email', '');

            return Limit::perMinute(5)->by(mb_strtolower($email).'|'.$request->ip());
        });

        RateLimiter::for('admin-password', function (Request $request) {
            return Limit::perMinute(5)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
