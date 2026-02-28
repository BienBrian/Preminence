# Multi-Tenant SaaS Conversion Plan

## Happy Church App → Church Management SaaS Platform

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Tenancy Strategy](#2-tenancy-strategy)
3. [Database Changes](#3-database-changes)
4. [Tenant Resolution & Middleware](#4-tenant-resolution--middleware)
5. [Auth & User Isolation](#5-auth--user-isolation)
6. [Spatie Roles/Permissions Per-Tenant](#6-spatie-rolespermissions-per-tenant)
7. [Integration Isolation (Mpesa, SMS, Email)](#7-integration-isolation-mpesa-sms-email)
8. [Mpesa Callback Routing](#8-mpesa-callback-routing)
9. [File Storage Isolation](#9-file-storage-isolation)
10. [SaaS Billing & Subscription Plans](#10-saas-billing--subscription-plans)
11. [Superadmin Monitoring Dashboard](#11-superadmin-monitoring-dashboard)
12. [Tenant Onboarding Flow](#12-tenant-onboarding-flow)
13. [Public Website Per-Tenant](#13-public-website-per-tenant)
14. [Migration Strategy (Existing Data)](#14-migration-strategy-existing-data)
15. [Phase Plan & Execution Order](#15-phase-plan--execution-order)

---

## 1. Architecture Overview

### Current State
- Single-tenant Laravel app serving one church
- ~70 database tables with NO tenant identifier
- Single `settings` row for church name/logo
- Hardcoded Mpesa paybill (186903)
- Single SMS API key, single email config
- One user pool, one role set

### Target State
- Multi-tenant SaaS where each church (tenant) has:
  - Its own subdomain: `churchname.happychurch.co.ke`
  - Isolated data (users, finances, contacts, SMS, etc.)
  - Own Mpesa paybill/till number
  - Own SMS sender credentials
  - Own roles and permissions
  - Own public website
- Superadmin panel at `admin.happychurch.co.ke` for platform monitoring
- Subscription-based billing with plan tiers

### Recommended Package
Use **[stancl/tenancy](https://tenancyforlaravel.com/)** v3 (Laravel multi-tenancy package):
- Handles tenant identification (subdomain, domain, path)
- Automatic DB switching or column-based scoping
- Event-driven tenant lifecycle
- Compatible with Spatie permissions

### Tenancy Model: **Single Database, Column-Based Isolation**
Why: The app has ~70 tables. Managing separate databases per church is operationally expensive at scale. Column-based isolation (`tenant_id` on every table) with Eloquent global scopes is simpler, allows cross-tenant reporting for superadmin, and is easier to maintain.

---

## 2. Tenancy Strategy

### Option A: Single Database + `tenant_id` Column (Recommended)
```
database: happychurch_saas
├── tenants (church registry)
├── users (tenant_id column)
├── funds (tenant_id column)
├── settings (tenant_id column)
└── ... all tables get tenant_id
```

**Pros:**
- Simple deployment, single DB connection
- Easy cross-tenant superadmin queries
- No DB provisioning on signup
- Shared migration management

**Cons:**
- Must never forget the `tenant_id` scope (mitigated by global scopes)
- Large tables may need composite indexes

### Option B: Database-Per-Tenant (Not Recommended for This App)
Would require 70+ tables duplicated per church. Operationally complex.

### Decision: Go with Option A.

---

## 3. Database Changes

### 3a. New Tables

```sql
-- Tenant registry
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,          -- "Happy Church Ruiru"
    slug VARCHAR(100) UNIQUE NOT NULL,   -- "happychurch-ruiru" (used in subdomain)
    domain VARCHAR(255) NULLABLE UNIQUE, -- custom domain support
    logo VARCHAR(255) NULLABLE,
    status ENUM('active','suspended','trial','cancelled') DEFAULT 'trial',
    plan_id BIGINT UNSIGNED NULLABLE,
    trial_ends_at TIMESTAMP NULLABLE,
    subscription_ends_at TIMESTAMP NULLABLE,
    owner_user_id BIGINT UNSIGNED,       -- the admin who created the church
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Subscription plans
CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,          -- "Free", "Starter", "Pro", "Enterprise"
    slug VARCHAR(50) UNIQUE NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    billing_cycle ENUM('monthly','yearly') DEFAULT 'monthly',
    max_users INT DEFAULT 50,
    max_sms_per_month INT DEFAULT 500,
    max_storage_mb INT DEFAULT 500,
    features JSON NULLABLE,              -- {"mpesa": true, "email": true, "website": true}
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Payment/subscription tracking
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active','past_due','cancelled','trial') DEFAULT 'trial',
    starts_at TIMESTAMP,
    ends_at TIMESTAMP,
    payment_method VARCHAR(50),          -- "mpesa", "card", "bank"
    payment_reference VARCHAR(255),
    amount DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Superadmin users (separate from tenant users)
CREATE TABLE super_admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Audit log for superadmin
CREATE TABLE platform_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULLABLE,
    super_admin_id BIGINT UNSIGNED NULLABLE,
    action VARCHAR(100),                 -- "tenant.created", "tenant.suspended", "plan.changed"
    details JSON NULLABLE,
    ip_address VARCHAR(45),
    created_at TIMESTAMP
);
```

### 3b. Add `tenant_id` to ALL Existing Tables

Every existing table needs a `tenant_id` column. Migration pattern:

```php
// One migration per batch of tables
Schema::table('users', function (Blueprint $table) {
    $table->unsignedBigInteger('tenant_id')->after('id')->default(1);
    $table->index('tenant_id');
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

**Tables requiring `tenant_id` (complete list):**

| Category | Tables |
|----------|--------|
| Core | `users`, `settings`, `integrations` |
| People | `contacts`, `scontacts`, `emergency_contact`, `church`, `families`, `professions`, `education`, `profiles`, `profile_categories` |
| Finance | `funds`, `sources`, `pledges`, `pledge_sms`, `assets`, `donations`, `budget`, `budget_items`, `activities`, `summaries`, `summaries_operations`, `ModeOfPayment` |
| Communication | `sms`, `sms_recipients`, `emails`, `email_recipients`, `schedules`, `twilios` |
| Events | `events`, `notices`, `seminars`, `attendance`, `attendance_groups`, `registration` |
| Spiritual | `sermons`, `prayers`, `testimonials` |
| People Groups | `communities`, `departments`, `people`, `people_members`, `participants`, `groups`, `pastors` |
| Content | `articles`, `article_categories`, `article_tags`, `galleries`, `home_pages`, `pastorsmessage`, `orderofservice`, `weeklyverse`, `media_folders`, `media_files` |
| Shop | `products`, `purchases` |
| Tags/Settings | `tags`, `tag_user`, `birthday_settings`, `sunday_school_classes`, `residences`, `mpesa_message_settings` |
| Mpesa | `mpesa_transactions`, `mpesa_phones`, `missing_mpesa_phones` |
| Auth | `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` |

**Tables that stay global (no tenant_id):**
- `tenants`, `plans`, `subscriptions`, `super_admins`, `platform_audit_log`
- `password_resets`, `password_reset_tokens`, `personal_access_tokens`, `failed_jobs`

### 3c. Composite Indexes

Add composite indexes for common queries:
```sql
ALTER TABLE users ADD INDEX idx_tenant_phone (tenant_id, phone);
ALTER TABLE users ADD INDEX idx_tenant_email (tenant_id, email);
ALTER TABLE funds ADD INDEX idx_tenant_created (tenant_id, created_at);
ALTER TABLE sms ADD INDEX idx_tenant_created (tenant_id, created_at);
```

### 3d. Unique Constraints Update

Currently `users.phone` has a unique constraint globally. Change to unique per-tenant:
```sql
ALTER TABLE users DROP INDEX users_phone_unique;
ALTER TABLE users ADD UNIQUE INDEX users_tenant_phone_unique (tenant_id, phone);
```

Same for `users.email`:
```sql
ALTER TABLE users ADD UNIQUE INDEX users_tenant_email_unique (tenant_id, email);
```

---

## 4. Tenant Resolution & Middleware

### Subdomain-Based Resolution

```
{slug}.happychurch.co.ke → resolves to tenant
admin.happychurch.co.ke  → superadmin panel
www.happychurch.co.ke    → marketing/landing page
```

### Middleware: `IdentifyTenant`

```php
class IdentifyTenant
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $slug = explode('.', $host)[0]; // "happychurch-ruiru"

        $tenant = Tenant::where('slug', $slug)
            ->orWhere('domain', $host) // custom domain support
            ->first();

        if (!$tenant) abort(404, 'Church not found');
        if ($tenant->status === 'suspended') abort(403, 'Account suspended');

        app()->instance('tenant', $tenant);
        config(['app.tenant_id' => $tenant->id]);

        return $next($request);
    }
}
```

### Registration in Kernel

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware ...
        \App\Http\Middleware\IdentifyTenant::class, // add before auth
    ],
];

protected $middlewareAliases = [
    // ... existing ...
    'superadmin' => \App\Http\Middleware\SuperAdmin::class,
    'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
    'tenant.plan' => \App\Http\Middleware\CheckPlanLimits::class,
];
```

---

## 5. Auth & User Isolation

### Tenant-Scoped User Model

```php
class User extends Authenticatable
{
    use HasRoles, BelongsToTenant;

    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($query) {
            if ($tenantId = config('app.tenant_id')) {
                $query->where('tenant_id', $tenantId);
            }
        });

        static::creating(function ($model) {
            if (!$model->tenant_id && config('app.tenant_id')) {
                $model->tenant_id = config('app.tenant_id');
            }
        });
    }
}
```

### BelongsToTenant Trait (applied to ALL tenant-scoped models)

```php
trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function ($query) {
            if ($tenantId = config('app.tenant_id')) {
                $query->where($query->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        static::creating(function ($model) {
            if (!$model->tenant_id && config('app.tenant_id')) {
                $model->tenant_id = config('app.tenant_id');
            }
        });
    }
}
```

### Login Flow Change

Login must scope user lookup to current tenant:
```php
// In LoginController or custom auth
$credentials = $request->only('email', 'password');
$credentials['tenant_id'] = config('app.tenant_id');
```

---

## 6. Spatie Roles/Permissions Per-Tenant

Spatie v5+ supports `team_id` for per-tenant role isolation.

### Config change (`config/permission.php`):
```php
'teams' => true,
'team_foreign_key' => 'tenant_id',
```

### Usage:
```php
// Assign role within tenant context
setPermissionsTeamId($tenant->id);
$user->assignRole('Admin');

// Check permission within tenant
$user->hasPermissionTo('View Finances'); // auto-scoped to tenant
```

### Default Roles Seeded Per-Tenant on Signup:
- Super Admin (church-level)
- Admin
- Editor
- Member

---

## 7. Integration Isolation (Mpesa, SMS, Email)

The `integrations` table already stores configs as JSON. Add `tenant_id`:

```php
// Each tenant configures their own:
Integration::create([
    'tenant_id' => $tenant->id,
    'type' => 'sms',
    'provider' => 'advanta',
    'config' => json_encode([
        'api_key' => '...',
        'partner_id' => '...',
        'short_code' => '...',
    ]),
    'is_active' => true,
]);
```

### SMS Sending Update:
```php
// Instead of env('SMS_API_KEY')
$smsConfig = Integration::where('tenant_id', tenant()->id)
    ->where('type', 'sms')
    ->where('is_active', true)
    ->first();
$apiKey = $smsConfig->config['api_key'];
```

### Email Config Per-Tenant:
Dynamically set mail config before sending:
```php
config(['mail.mailers.smtp.host' => $emailConfig->config['host']]);
config(['mail.mailers.smtp.username' => $emailConfig->config['username']]);
// etc.
```

---

## 8. Mpesa Callback Routing

### Challenge
Mpesa sends callbacks to a single URL. With multiple tenants using different paybills/till numbers, we need to route callbacks to the correct tenant.

### Solution: Route by BusinessShortCode

```php
// api.php — callback remains single endpoint
Route::post('/api/transaction/confirmation', [MpesaAPIController::class, 'mpesaConfirmation']);

// In MpesaAPIController::mpesaConfirmation()
public function mpesaConfirmation(Request $request)
{
    $shortcode = $request->input('BusinessShortCode');

    // Find tenant by their Mpesa shortcode
    $integration = Integration::where('type', 'mpesa')
        ->whereJsonContains('config->shortcode', $shortcode)
        ->first();

    if (!$integration) {
        Log::error('Mpesa callback: unknown shortcode ' . $shortcode);
        return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Rejected']);
    }

    // Set tenant context for the request
    $tenant = Tenant::find($integration->tenant_id);
    app()->instance('tenant', $tenant);
    config(['app.tenant_id' => $tenant->id]);

    // Process as before (scoped to tenant)
    // ...
}
```

### C2B URL Registration Per-Tenant:
Each tenant registers their own callback URLs during onboarding:
```
Confirmation URL: https://api.happychurch.co.ke/api/transaction/confirmation
```
The same endpoint serves all tenants; routing happens by `BusinessShortCode`.

---

## 9. File Storage Isolation

### Directory Structure:
```
storage/app/tenants/{tenant_id}/
├── profile_images/
├── website/
├── gallery/
├── media/
├── articles/
└── sermons/
```

### Implementation:
```php
// Helper function
function tenant_storage_path($path = '')
{
    return storage_path('app/tenants/' . config('app.tenant_id') . '/' . $path);
}

// Asset serving
Route::get('/tenant-assets/{path}', function ($path) {
    $fullPath = tenant_storage_path($path);
    if (!file_exists($fullPath)) abort(404);
    return response()->file($fullPath);
})->where('path', '.*');
```

---

## 10. SaaS Billing & Subscription Plans

### Plan Tiers

| Plan | Price (KES/mo) | Users | SMS/mo | Storage | Features |
|------|----------------|-------|--------|---------|----------|
| Free | 0 | 20 | 50 | 100MB | Basic: members, attendance |
| Starter | 2,000 | 100 | 500 | 1GB | + Finances, SMS, Email |
| Pro | 5,000 | 500 | 2,000 | 5GB | + Mpesa, Website, Shop |
| Enterprise | 15,000 | Unlimited | 10,000 | 50GB | + API, Custom domain, Priority support |

### Feature Gating Middleware

```php
class CheckPlanLimits
{
    public function handle($request, Closure $next, $feature)
    {
        $tenant = app('tenant');
        $plan = $tenant->plan;

        if ($feature === 'mpesa' && !($plan->features['mpesa'] ?? false)) {
            return redirect()->back()->with('error', 'Upgrade to Pro for Mpesa integration.');
        }

        if ($feature === 'user_limit') {
            $count = User::where('tenant_id', $tenant->id)->count();
            if ($count >= $plan->max_users) {
                return redirect()->back()->with('error', 'User limit reached. Upgrade your plan.');
            }
        }

        return $next($request);
    }
}
```

### Payment Collection via Mpesa (STK Push)
Use one central paybill for SaaS subscription payments:
```
Platform Paybill: (new dedicated one)
Account Ref: tenant slug + plan
```

---

## 11. Superadmin Monitoring Dashboard

### Access
- URL: `admin.happychurch.co.ke`
- Separate auth guard (`super_admins` table)
- Completely isolated from tenant dashboards

### Superadmin Dashboard Screens

#### 11a. Overview Dashboard (`/superadmin`)
```
┌─────────────────────────────────────────────────────┐
│  Platform Overview                                   │
├──────────┬──────────┬──────────┬───────────────────── │
│ Churches │ Users    │ Revenue  │ SMS Sent (30d)       │
│ 142      │ 8,450   │ KES 520K │ 45,200               │
├──────────┴──────────┴──────────┴───────────────────── │
│                                                       │
│  Revenue Chart (last 12 months)                      │
│  ████████████████████████████                        │
│                                                       │
│  Recent Signups              Expiring Trials          │
│  • Grace Church (2h ago)     • Faith Chapel (2 days)  │
│  • Jubilee Parish (1d ago)   • Hope Center (5 days)   │
│                                                       │
│  Active Issues                                        │
│  • 3 churches with payment overdue                    │
│  • 2 churches approaching SMS limit                   │
│  • 1 Mpesa callback error (last 24h)                 │
└───────────────────────────────────────────────────────┘
```

#### 11b. Tenants List (`/superadmin/tenants`)
- DataTable with: Name, Slug, Plan, Status, Users, Created, Last Active
- Filters: by plan, status, date range
- Actions: View, Suspend, Activate, Delete, Impersonate (login as church admin)
- Bulk actions: Send notification, Change plan

#### 11c. Tenant Detail (`/superadmin/tenants/{id}`)
- Church info, admin contact, plan details
- Usage stats: users, SMS sent, storage used, Mpesa transactions
- Activity timeline
- Integration status (Mpesa connected?, SMS balance?)
- Actions: Suspend, Upgrade/Downgrade plan, Extend trial, Impersonate
- Audit log for this tenant

#### 11d. Plans & Pricing (`/superadmin/plans`)
- CRUD for subscription plans
- Feature toggle matrix
- Active subscriber count per plan

#### 11e. Revenue & Billing (`/superadmin/billing`)
- Revenue overview: MRR, ARR, churn rate
- Payment history table
- Failed payments list
- Invoice generation

#### 11f. SMS Monitor (`/superadmin/sms`)
- Platform-wide SMS stats
- Per-tenant SMS usage
- SMS delivery rate
- Alerts for tenants approaching limits

#### 11g. Mpesa Monitor (`/superadmin/mpesa`)
- Callback success/failure rate
- Unresolved callbacks (missing tenant match)
- Per-tenant transaction volume
- Error log

#### 11h. Platform Settings (`/superadmin/settings`)
- Default plans configuration
- Default roles seeded for new tenants
- Email templates (welcome, trial ending, payment failed)
- SMS provider master credentials
- Mpesa platform paybill config
- Feature flags

#### 11i. Audit Log (`/superadmin/audit`)
- All platform-level actions
- Filterable by tenant, admin, action type, date
- Export to CSV

### Superadmin Routes Structure

```php
Route::domain('admin.happychurch.co.ke')->group(function () {
    Route::get('login', [SuperAdminAuthController::class, 'showLogin']);
    Route::post('login', [SuperAdminAuthController::class, 'login']);

    Route::middleware(['auth:superadmin'])->prefix('superadmin')->group(function () {
        Route::get('/', [SuperAdminController::class, 'dashboard']);
        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{id}/suspend', [TenantController::class, 'suspend']);
        Route::post('tenants/{id}/activate', [TenantController::class, 'activate']);
        Route::post('tenants/{id}/impersonate', [TenantController::class, 'impersonate']);
        Route::resource('plans', PlanController::class);
        Route::get('billing', [BillingController::class, 'index']);
        Route::get('sms', [SmsMonitorController::class, 'index']);
        Route::get('mpesa', [MpesaMonitorController::class, 'index']);
        Route::get('audit', [AuditController::class, 'index']);
        Route::get('settings', [PlatformSettingsController::class, 'index']);
    });
});
```

---

## 12. Tenant Onboarding Flow

### Step 1: Registration
```
www.happychurch.co.ke/register
├── Church Name
├── Admin Name
├── Admin Email
├── Admin Phone
├── Password
└── Plan selection (Free / Starter / Pro)
```

### Step 2: Automated Provisioning
```php
// On registration:
1. Create tenant record (slug auto-generated from name)
2. Set up default settings row
3. Create admin user with 'Super Admin' role
4. Seed default roles & permissions
5. Create default fund sources (Tithe, Offering, etc.)
6. Create default attendance groups
7. Send welcome email with login URL
8. Start 14-day trial if paid plan selected
```

### Step 3: First Login
```
churchname.happychurch.co.ke/login
├── Setup wizard:
│   ├── Upload church logo
│   ├── Configure Mpesa (optional)
│   ├── Configure SMS (optional)
│   ├── Invite team members
│   └── Done!
```

---

## 13. Public Website Per-Tenant

Each tenant can have a public-facing website:
```
churchname.happychurch.co.ke/          → church website (if enabled)
churchname.happychurch.co.ke/login     → dashboard login
churchname.happychurch.co.ke/dashboard → admin dashboard
```

The current public routes (`/`, `/people`, `/noticeboard`, etc.) become tenant-scoped, reading from the tenant's `settings`, `galleries`, `events`, etc.

### Custom Domain Support
```
www.happychurchruiru.org → CNAME to happychurch-ruiru.happychurch.co.ke
```
Resolved via the `tenants.domain` column in the `IdentifyTenant` middleware.

---

## 14. Migration Strategy (Existing Data)

### For the Current Happy Church Ruiru Instance:

1. Create `tenants` table, insert tenant #1 (Happy Church Ruiru)
2. Run migration to add `tenant_id` DEFAULT 1 to all existing tables
3. Make `tenant_id` NOT NULL after backfill
4. Add indexes
5. Update unique constraints (phone, email → per-tenant unique)
6. Create superadmin account
7. Update DNS: `happychurch-ruiru.happychurch.co.ke` → existing server

### Data Safety:
- All existing data gets `tenant_id = 1`
- No data loss
- Existing users continue working at the new subdomain
- Old URL can redirect to new subdomain

---

## 15. Phase Plan & Execution Order

### Phase 1: Foundation (Week 1-2)
- [ ] Install stancl/tenancy or build custom tenant resolution
- [ ] Create `tenants`, `plans`, `super_admins` tables
- [ ] Create `IdentifyTenant` middleware
- [ ] Create `BelongsToTenant` trait
- [ ] Add `tenant_id` to `users`, `settings`, `integrations` (start with core tables)
- [ ] Update User model with global scope
- [ ] Update login to scope by tenant
- [ ] Test: two tenants can coexist without data leakage

### Phase 2: Full Table Migration (Week 2-3)
- [ ] Add `tenant_id` to ALL remaining tables (~65 tables)
- [ ] Apply `BelongsToTenant` trait to all models
- [ ] Update all `DB::table()` raw queries to include `tenant_id` filter
- [ ] Update unique constraints (phone, email)
- [ ] Add composite indexes
- [ ] Update all controllers to verify tenant scoping

### Phase 3: Integration Isolation (Week 3-4)
- [ ] Scope SMS config per-tenant (integrations table)
- [ ] Scope Email config per-tenant
- [ ] Scope Mpesa config per-tenant
- [ ] Update Mpesa callback to resolve tenant by shortcode
- [ ] Update SMS sending to use tenant's credentials
- [ ] File storage isolation (tenant_id directories)

### Phase 4: Superadmin Panel (Week 4-5)
- [ ] Build superadmin auth (separate guard)
- [ ] Overview dashboard with stats
- [ ] Tenants CRUD + suspend/activate
- [ ] Plans CRUD
- [ ] Tenant detail page with usage stats
- [ ] Impersonation feature
- [ ] Audit log

### Phase 5: Billing & Subscription (Week 5-6)
- [ ] Plans table + subscription tracking
- [ ] Mpesa STK push for subscription payments
- [ ] Feature gating middleware
- [ ] Trial expiry handling (cron job)
- [ ] Payment reminder emails/SMS
- [ ] Invoice generation

### Phase 6: Onboarding & Polish (Week 6-7)
- [ ] Self-service registration page
- [ ] Automated tenant provisioning
- [ ] Setup wizard for new tenants
- [ ] Welcome email templates
- [ ] Custom domain support
- [ ] Landing/marketing page at www.happychurch.co.ke

### Phase 7: Testing & Launch (Week 7-8)
- [ ] Cross-tenant data isolation audit
- [ ] Load testing with multiple tenants
- [ ] Mpesa callback testing with multiple shortcodes
- [ ] SMS sending per-tenant testing
- [ ] Migrate existing Happy Church Ruiru as tenant #1
- [ ] DNS setup and SSL certificates
- [ ] Go live

---

## Key Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| Data leakage between tenants | Global scopes on ALL models + automated test suite that verifies isolation |
| Forgetting `tenant_id` in raw queries | Grep codebase for `DB::table(` and `DB::select(` — convert all to Eloquent or add manual scoping |
| Mpesa callback routing failure | Log all callbacks; fallback to `missing_mpesa_phones` equivalent at platform level |
| Performance at scale (single DB) | Composite indexes; read replicas if needed; table partitioning by `tenant_id` |
| Spatie permissions conflict | Enable team mode (`teams => true`); test role assignment isolation |
| Existing session conflicts | Subdomain-scoped cookies: `.happychurch.co.ke` |

---

## Technology Additions

| Component | Technology |
|-----------|-----------|
| Multi-tenancy | stancl/tenancy v3 OR custom (trait + middleware) |
| Billing | Custom Mpesa STK integration (reuse existing code) |
| Superadmin UI | AdminLTE (same as dashboard) with separate guard |
| DNS/SSL | Wildcard cert for `*.happychurch.co.ke` (Let's Encrypt) |
| Queue | Redis + Laravel Horizon (for async tenant provisioning, email/SMS sending) |
| Monitoring | Laravel Telescope (dev) + custom audit log (production) |
