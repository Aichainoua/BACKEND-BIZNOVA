<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class FeatureServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (glob(app_path('Features/*/database/migrations'), GLOB_ONLYDIR) ?: [] as $migrationsPath) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        RateLimiter::for('auth', function (Request $request): Limit {
            $email = $request->input('email');
            $email = is_string($email) ? Str::lower($email) : 'invalid';

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
