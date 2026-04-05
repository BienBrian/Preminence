<?php

/**
 * Pisti Module Marketplace Configuration
 */

return [
    /**
     * Cache configuration for module data.
     */
    'cache_ttl_minutes' => env('MODULE_CACHE_TTL', 5),

    /**
     * Installation queue settings.
     */
    'installation' => [
        'queue' => env('MODULE_INSTALL_QUEUE', 'default'),
        'timeout' => env('MODULE_INSTALL_TIMEOUT', 300), // 5 minutes
        'retry_after' => env('MODULE_INSTALL_RETRY_AFTER', 3600), // 1 hour
    ],

    /**
     * Billing settings.
     */
    'billing' => [
        'proration_enabled' => env('MODULE_PRORATION_ENABLED', true),
        'grace_period_days' => env('MODULE_BILLING_GRACE_DAYS', 3),
        'auto_suspend_on_failure' => env('MODULE_AUTO_SUSPEND', true),
        'retry_attempts' => env('MODULE_BILLING_RETRIES', 3),
        'retry_delay_hours' => env('MODULE_BILLING_RETRY_DELAY', 24),
    ],

    /**
     * Trial settings.
     */
    'trial' => [
        'default_days' => env('MODULE_TRIAL_DAYS', 14),
        'require_payment_method' => env('MODULE_TRIAL_REQUIRES_PAYMENT', false),
        'auto_convert' => env('MODULE_TRIAL_AUTO_CONVERT', true),
    ],

    /**
     * Feature flags for marketplace.
     */
    'features' => [
        'marketplace_enabled' => env('MODULE_MARKETPLACE_ENABLED', true),
        'self_service_install' => env('MODULE_SELF_SERVICE_ENABLED', true),
        'approval_workflow' => env('MODULE_APPROVAL_WORKFLOW', false),
        'allow_downgrades' => env('MODULE_ALLOW_DOWNGRADES', true),
        'usage_tracking' => env('MODULE_USAGE_TRACKING', true),
    ],

    /**
     * Default limits for modules.
     */
    'default_limits' => [
        'max_addons_per_tenant' => env('MODULE_MAX_ADDONS', 0), // 0 = unlimited
        'max_installs_per_hour' => env('MODULE_MAX_INSTALLS_PER_HOUR', 10),
        'max_trials_per_module' => env('MODULE_MAX_TRIALS', 1),
    ],

    /**
     * Module paths.
     */
    'paths' => [
        'migrations' => database_path('migrations/modules'),
        'seeders' => database_path('seeders/modules'),
        'views' => resource_path('views/modules'),
    ],
];
