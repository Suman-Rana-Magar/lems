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
        // Force HTTPS if explicitly set in env or if APP_URL uses https/ngrok
        $forceHttps = env('FORCE_HTTPS', false);
        $appUrl = env('APP_URL', 'http://localhost');
        
        // Check if behind proxy (ngrok) or APP_URL is https
        // Ngrok always uses HTTPS, so detect it automatically
        $isHttps = $forceHttps 
            || str_starts_with($appUrl, 'https://')
            || str_contains($appUrl, 'ngrok.io')
            || str_contains($appUrl, 'ngrok-free.app');
        
        if ($isHttps) {
            URL::forceScheme('https');
        }
    }
}
