<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrap();
        
        // Force HTTPS if required by environment configuration
        // Use the pisti config helper to determine if HTTPS should be forced
        if (should_force_https()) {
            \URL::forceScheme('https');
        }
    }
}
