<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Route job failure notifications to Pisti platform team
        // Horizon::routeMailNotificationsTo('platform@pisti.co.ke');
        // Horizon::routeSlackNotificationsTo(env('HORIZON_SLACK_WEBHOOK'), '#alerts');
    }

    /**
     * Register the Horizon gate.
     *
     * In local, always accessible. In production, only super admins can view it.
     * The SuperAdmin model will be added in Phase 1.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            // Local: always accessible
            if (app()->environment('local')) {
                return true;
            }

            // Production: only authenticated super admins
            // (SuperAdmin model + guard added in Phase 1)
            return $user && $user instanceof \App\Models\SuperAdmin;
        });
    }
}
