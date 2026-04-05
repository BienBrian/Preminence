<?php

/**
 * Pisti Platform Configuration
 *
 * Centralized environment-specific settings for the Pisti SaaS platform.
 * All settings can be controlled via .env file using PISTI_* variables.
 *
 * Environment Modes:
 * - 'dev': Local development (path-based routing, HTTP allowed, debug enabled)
 * - 'staging': Testing environment (subdomain-based, HTTPS required, some debug)
 * - 'production': Live environment (subdomain-based, HTTPS required, no debug)
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Environment Mode
    |--------------------------------------------------------------------------
    |
    | The current environment mode for the Pisti platform.
    | Controls default behavior for routing, security, and features.
    |
    | Supported: 'dev', 'staging', 'production'
    |
    */
    'env' => env('PISTI_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Platform Domain
    |--------------------------------------------------------------------------
    |
    | The main domain used for the platform. This is used to construct
    | subdomain URLs (e.g., {tenant}.happychurchruiru.org).
    |
    */
    'platform_domain' => env('PISTI_PLATFORM_DOMAIN', 'happychurchruiru.org'),

    /*
    |--------------------------------------------------------------------------
    | Superadmin Routing Mode
    |--------------------------------------------------------------------------
    |
    | Determines how superadmin routes are accessed:
    | - 'subdomain': Access via superadmin.{platform_domain}
    | - 'path': Access via {app_url}/superadmin
    | - 'auto': Determine based on PISTI_ENV (dev=path, staging|production=subdomain)
    |
    */
    'superadmin_mode' => env('SUPERADMIN_MODE', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS/SSL Settings
    |--------------------------------------------------------------------------
    |
    | Controls whether HTTPS is enforced:
    | - true: Force HTTPS for all URLs
    | - false: Allow HTTP
    | - 'auto': Force HTTPS only in staging/production
    |
    */
    'force_https' => env('FORCE_HTTPS', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific features per environment.
    | Comma-separated list in .env: PISTI_FEATURES=impersonation,debug_bar
    |
    | Available features:
    | - impersonation: Allow superadmin to impersonate tenants
    | - debug_bar: Show debug bar (if DEBUG_BAR_ENABLED is also true)
    | - query_log: Log all database queries
    | - email_preview: Intercept emails and show preview instead of sending
    | - maintenance_mode: Show maintenance page for non-superadmins
    | - registration_open: Allow new tenant registrations
    | - demo_mode: Enable demo data and reset functionality
    |
    */
    'features' => explode(',', env('PISTI_FEATURES', '')),

    /*
    |--------------------------------------------------------------------------
    | Tenant Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for tenant resolution and behavior.
    |
    */
    'tenant' => [
        // Default tenant ID when no tenant is resolved (local dev fallback)
        'default_id' => env('PISTI_TENANT_DEFAULT_ID', 1),

        // Allow tenant override via ?__tenant={slug} query parameter
        'allow_query_override' => env('PISTI_TENANT_QUERY_OVERRIDE', true),

        // Subdomains that bypass tenant resolution (reserved subdomains)
        'reserved_subdomains' => [
            'www',
            'admin',
            'api',
            'staging',
            'horizon',
            'superadmin',
            'mail',
            'ftp',
            'smtp',
            'pop',
            'imap',
            'webmail',
            'webdisk',
            'cpanel',
            'whm',
            'ns1',
            'ns2',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Superadmin Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the superadmin panel.
    |
    */
    'superadmin' => [
        // Subdomain used for superadmin access
        'subdomain' => env('PISTI_SUPERADMIN_SUBDOMAIN', 'superadmin'),

        // Path prefix when using path-based routing
        'path_prefix' => env('PISTI_SUPERADMIN_PATH_PREFIX', 'superadmin'),

        // Session lifetime in minutes
        'session_lifetime' => env('PISTI_SUPERADMIN_SESSION_LIFETIME', 120),

        // Require 2FA for superadmin login
        'require_2fa' => env('PISTI_SUPERADMIN_REQUIRE_2FA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers per environment.
    |
    */
    'security' => [
        // Frame options: 'DENY', 'SAMEORIGIN', or false to disable
        'x_frame_options' => env('PISTI_SECURITY_FRAME_OPTIONS', 'SAMEORIGIN'),

        // Content Security Policy: true, false, or custom string
        'csp_enabled' => env('PISTI_SECURITY_CSP', false),

        // Strict Transport Security max-age (only applies when HTTPS is forced)
        'hsts_max_age' => env('PISTI_SECURITY_HSTS_MAX_AGE', 31536000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits per environment (requests per minute).
    |
    */
    'rate_limits' => [
        'login' => (int) env('PISTI_RATE_LIMIT_LOGIN', 5),
        'api' => (int) env('PISTI_RATE_LIMIT_API', 60),
        'sms' => (int) env('PISTI_RATE_LIMIT_SMS', 10),
        'email' => (int) env('PISTI_RATE_LIMIT_EMAIL', 10),
    ],

];
