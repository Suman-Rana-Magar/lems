<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Force HTTPS if explicitly set in env or if APP_URL uses https
        $forceHttps = env('FORCE_HTTPS', false);
        $appUrl = env('APP_URL', 'http://localhost');
        
        if ($forceHttps || str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
