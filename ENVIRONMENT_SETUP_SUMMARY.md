# Environment Configuration Setup - Summary

## Overview
A centralized environment configuration system has been created to easily toggle between development and production settings.

## Files Created/Modified

### 1. `.env` - New Environment Variables
```env
PISTI_ENV=dev                          # dev | staging | production
SUPERADMIN_MODE=auto                   # auto | path | subdomain
PISTI_PLATFORM_DOMAIN=your-domain.com
FORCE_HTTPS=auto                       # auto | true | false
PISTI_FEATURES=impersonation,debug_bar,query_log
```

### 2. `config/pisti.php` - Centralized Config
Central configuration file for all environment-specific settings.

### 3. `app/Helpers/helpers.php` - Helper Functions
New helper functions added:
- `is_dev_mode()` / `is_staging_mode()` / `is_production_mode()`
- `is_subdomain_mode()` / `is_path_mode()`
- `should_force_https()`
- `feature_enabled('feature_name')`
- `superadmin_url()` / `superadmin_subdomain()` / `superadmin_path_prefix()`
- `pisti_platform_domain()` / `tenant_url()`
- `pisti_config('key')`

### 4. `routes/web.php` - Updated Superadmin Routes
Superadmin routing now automatically adjusts based on `PISTI_ENV`:
- **Dev mode**: Accessible at `/superadmin/*`
- **Staging/Production**: Accessible at `superadmin.your-domain.com/*`

### 5. `app/Providers/AppServiceProvider.php` - HTTPS Logic
HTTPS is now only forced in staging/production (controlled by `PISTI_ENV`).

### 6. `app/Http/Middleware/IdentifyTenant.php` - Updated Middleware
Uses config-based domain and subdomain settings.

## Usage Examples

### Check Environment
```php
if (is_dev_mode()) {
    // Only run in development
}

if (should_force_https()) {
    // Force HTTPS
}
```

### Check Features
```php
if (feature_enabled('impersonation')) {
    // Show impersonation button
}
```

### Generate URLs
```php
$superadminUrl = superadmin_url();           // Auto-detects correct URL
$superadminUrl = superadmin_url('dashboard'); // With path
$tenantUrl = tenant_url('church-slug');       // Tenant subdomain URL
```

## Switching Environments

### For Local Development
```env
PISTI_ENV=dev
SUPERADMIN_MODE=auto
FORCE_HTTPS=auto
PISTI_FEATURES=impersonation,debug_bar,query_log
```
Access superadmin at: `http://localhost:8000/superadmin`

### For Production
```env
PISTI_ENV=production
SUPERADMIN_MODE=auto
FORCE_HTTPS=auto
PISTI_FEATURES=
```
Access superadmin at: `https://superadmin.your-domain.com`

### Force Specific Modes
```env
# Force path mode (even in production)
SUPERADMIN_MODE=path

# Force subdomain mode (even in dev)
SUPERADMIN_MODE=subdomain

# Force HTTPS (even in dev)
FORCE_HTTPS=true

# Disable HTTPS (even in production - not recommended)
FORCE_HTTPS=false
```

## After Making Changes

Always clear caches after changing `.env`:

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

## Full Documentation
See `ENVIRONMENT_CONFIGURATION.md` for complete documentation.
