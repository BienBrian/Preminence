# Pisti Environment Configuration Guide

This guide explains how to configure environment-specific settings for the Pisti SaaS platform.

## Quick Start

### For Local Development
```env
PISTI_ENV=dev
SUPERADMIN_MODE=auto  # Will use path-based /superadmin
FORCE_HTTPS=auto      # Will allow HTTP
PISTI_FEATURES=impersonation,debug_bar,query_log
```

### For Staging
```env
PISTI_ENV=staging
SUPERADMIN_MODE=auto  # Will use subdomain-based superadmin.your-domain.com
FORCE_HTTPS=auto      # Will force HTTPS
PISTI_FEATURES=impersonation,email_preview
```

### For Production
```env
PISTI_ENV=production
SUPERADMIN_MODE=auto  # Will use subdomain-based superadmin.your-domain.com
FORCE_HTTPS=auto      # Will force HTTPS
PISTI_FEATURES=
```

## Configuration Variables

### Core Environment

| Variable | Options | Default | Description |
|----------|---------|---------|-------------|
| `PISTI_ENV` | `dev`, `staging`, `production` | `production` | Main environment mode |
| `PISTI_PLATFORM_DOMAIN` | Your domain | `happychurchruiru.org` | Base domain for subdomains |

### Superadmin Routing

| Variable | Options | Default | Description |
|----------|---------|---------|-------------|
| `SUPERADMIN_MODE` | `auto`, `path`, `subdomain` | `auto` | How superadmin routes are accessed |

**Modes:**
- `path`: Access via `http://localhost:8000/superadmin`
- `subdomain`: Access via `https://superadmin.your-domain.com`
- `auto`: Automatically chooses based on `PISTI_ENV`:
  - `dev` → path mode
  - `staging`/`production` → subdomain mode

### Security

| Variable | Options | Default | Description |
|----------|---------|---------|-------------|
| `FORCE_HTTPS` | `auto`, `true`, `false` | `auto` | Whether to force HTTPS |

**Options:**
- `true`: Always force HTTPS
- `false`: Never force HTTPS
- `auto`: Force HTTPS in staging/production only

### Feature Flags

| Variable | Format | Description |
|----------|--------|-------------|
| `PISTI_FEATURES` | Comma-separated list | Enable specific features |

**Available Features:**
- `impersonation` - Allow superadmin to impersonate tenant users
- `debug_bar` - Show Laravel debug bar (requires debugbar package)
- `query_log` - Log all database queries
- `email_preview` - Intercept emails and show preview
- `maintenance_mode` - Enable maintenance mode for non-superadmins
- `registration_open` - Allow new tenant registrations
- `demo_mode` - Enable demo data and reset functionality

## Helper Functions

Use these helper functions throughout your code for environment-aware logic:

### Environment Checks
```php
// Check current environment mode
is_dev_mode();           // true when PISTI_ENV=dev
is_staging_mode();       // true when PISTI_ENV=staging
is_production_mode();    // true when PISTI_ENV=production
```

### Routing Mode
```php
// Check superadmin routing mode
is_subdomain_mode();     // true if using subdomain-based routing
is_path_mode();          // true if using path-based routing

// Get superadmin URLs
superadmin_url();                    // Base superadmin URL
superadmin_url('dashboard');         // With path
superadmin_subdomain();              // superadmin.your-domain.com
superadmin_path_prefix();            // "superadmin"
```

### Security
```php
// HTTPS check
should_force_https();    // true if HTTPS should be forced

// Generate secure assets
pisti_asset('js/app.js');  // Asset with proper scheme
```

### Feature Flags
```php
// Check if feature is enabled
if (feature_enabled('impersonation')) {
    // Show impersonation button
}

if (feature_enabled('debug_bar')) {
    // Enable debug features
}
```

### Tenant Resolution
```php
// Get platform domain
$domain = pisti_platform_domain();  // your-domain.com

// Generate tenant URLs
$url = tenant_url('church-slug');
$url = tenant_url('church-slug', 'dashboard');

// Check reserved subdomains
if (is_reserved_subdomain('admin')) {
    // This is a reserved subdomain
}
```

### General Config
```php
// Access any pisti config value
$defaultId = pisti_config('tenant.default_id');
$rateLimit = pisti_config('rate_limits.login', 5);
```

## Examples

### Blade Templates
```blade
@if(is_dev_mode())
    <div class="debug-banner">Development Mode</div>
@endif

@if(feature_enabled('impersonation'))
    <a href="{{ superadmin_url('impersonate/' . $user->id) }}">Impersonate User</a>
@endif

<!-- Secure asset that respects HTTPS settings -->
<script src="{{ pisti_asset('js/app.js') }}"></script>
```

### Controllers
```php
public function store(Request $request)
{
    // Different validation for dev mode
    if (!is_dev_mode()) {
        $request->validate(['recaptcha' => 'required']);
    }
    
    // Feature-gated functionality
    if (feature_enabled('query_log')) {
        DB::enableQueryLog();
    }
    
    // ...
}
```

### Routes
```php
// Conditional routes based on environment
if (is_dev_mode()) {
    Route::get('/debug/users', [DebugController::class, 'users']);
}

// Feature-gated routes
Route::middleware(['feature:impersonation'])->group(function () {
    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start']);
});
```

## Migration from Old System

If you were previously using `env('APP_ENV')` checks:

**Before:**
```php
if (env('APP_ENV') === 'local') {
    // Development only
}
```

**After:**
```php
if (is_dev_mode()) {
    // Development only
}
```

**Before:**
```php
if (config('app.env') === 'production') {
    \URL::forceScheme('https');
}
```

**After:**
```php
if (should_force_https()) {
    \URL::forceScheme('https');
}
```

## Troubleshooting

### Superadmin not accessible
1. Check `PISTI_ENV` is set correctly
2. Check `SUPERADMIN_MODE` - if `auto`, it uses path for dev, subdomain for production
3. If using subdomain mode locally, add to your hosts file:
   ```
   127.0.0.1 superadmin.your-domain.test
   ```

### HTTPS issues in development
Set `FORCE_HTTPS=false` or `FORCE_HTTPS=auto` with `PISTI_ENV=dev`

### Changes not applying
Clear all caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
composer dump-autoload
```

## Advanced Configuration

### Custom Environment Logic
You can add custom logic in `config/pisti.php` or create new helper functions in `app/Helpers/helpers.php`.

### Adding New Features
1. Add the feature to the `PISTI_FEATURES` comment in `.env`
2. Use `feature_enabled('your_feature')` in your code
3. Document it in this guide
