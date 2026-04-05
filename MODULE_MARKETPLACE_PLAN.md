# Pisti Module Marketplace - Implementation Plan

## Document Information

| Field | Value |
|-------|-------|
| Version | 2.0 |
| Status | Implementation Ready |
| Last Updated | March 27, 2026 |
| Author | Senior Development Team |

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Architecture Philosophy](#architecture-philosophy)
3. [Phase 1: Foundation & Core Infrastructure](#phase-1-foundation--core-infrastructure)
4. [Phase 2: Domain Layer & Business Logic](#phase-2-domain-layer--business-logic)
5. [Phase 3: Marketplace Services](#phase-3-marketplace-services)
6. [Phase 4: SuperAdmin Interface](#phase-4-superadmin-interface)
7. [Phase 5: Tenant Marketplace](#phase-5-tenant-marketplace)
8. [Phase 6: Billing Integration](#phase-6-billing-integration)
9. [Phase 7: Performance & Observability](#phase-7-performance--observability)
10. [Appendices](#appendices)

---

## Executive Summary

This document provides a comprehensive, production-ready implementation plan for transforming Pisti from a static plan-based module system to a dynamic, self-service Module Marketplace. The plan prioritizes:

- **Scalability**: Horizontal scaling ready, multi-layer caching, read replicas
- **Reliability**: Transactional integrity, idempotency, circuit breakers, graceful degradation
- **Maintainability**: Clean architecture, interface-based design, comprehensive testing
- **Security**: Defense in depth, audit trails, input validation, rate limiting

---

## Architecture Philosophy

### Design Principles

```
┌─────────────────────────────────────────────────────────────────────┐
│                    CLEAN ARCHITECTURE LAYERS                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  PRESENTATION LAYER (Controllers, Views, API Resources)      │   │
│  │  - SuperAdmin ModuleController                               │   │
│  │  - MarketplaceController                                     │   │
│  │  - API Resources                                             │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                              │                                       │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  APPLICATION LAYER (Services, DTOs, Commands)                │   │
│  │  - ModuleInstaller                                           │   │
│  │  - MarketplaceService                                        │   │
│  │  - PricingCalculator                                         │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                              │                                       │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  DOMAIN LAYER (Models, Value Objects, Domain Events)         │   │
│  │  - Module (Aggregate Root)                                   │   │
│  │  - TenantModuleSubscription                                  │   │
│  │  - PlanModule                                                │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                              │                                       │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  INFRASTRUCTURE LAYER (Repositories, Cache, Queue)           │   │
│  │  - ModuleRepository                                          │   │
│  │  - CachedModuleRepository (Decorator)                        │   │
│  │  - ModuleInstallationJob                                     │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Key Architectural Decisions

| Decision | Rationale | Trade-offs |
|----------|-----------|------------|
| **Event-Driven Architecture** | Loose coupling, audit trail, async processing | Eventual consistency complexity |
| **Repository Pattern** | Testability, database abstraction | Additional abstraction layer |
| **CQRS for Marketplace Reads** | Scale reads independently, complex query optimization | Code duplication |
| **Multi-Layer Caching** | Performance at scale | Cache invalidation complexity |
| **Idempotency Keys** | Prevent duplicate billing | Storage overhead |

---

## Phase 1: Foundation & Core Infrastructure

### 1.1 Database Schema Design

#### Migration: `modules` (Master Registry)

```php
<?php
// database/migrations/2026_03_28_000001_create_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Module Identification
            $table->string('key', 50)->unique();           // 'finance', 'events'
            $table->string('name', 100);                    // Display name
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 255)->nullable();
            
            // Categorization & Discovery
            $table->string('category', 50)->index();        // 'core', 'standard', 'premium'
            $table->json('tags')->nullable();               // ['giving', 'reporting']
            $table->integer('sort_order')->default(0);
            
            // Versioning & Compatibility
            $table->string('version', 20)->default('1.0.0');
            $table->string('min_platform_version', 20)->nullable();
            $table->string('max_platform_version', 20)->nullable();
            
            // Dependencies (JSON array of module keys)
            $table->json('dependencies')->nullable();
            $table->json('conflicts')->nullable();          // Modules that conflict
            
            // Pricing Configuration
            $table->boolean('is_free')->default(true)->index();
            $table->decimal('price_monthly', 10, 2)->nullable();
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->enum('billing_model', ['flat', 'per_user', 'usage_based'])->default('flat');
            
            // Feature Flags & Limits (within module)
            $table->json('features')->nullable();           // Granular feature flags
            $table->json('default_limits')->nullable();     // {'sms_per_month': 1000}
            
            // Marketplace Presentation
            $table->string('icon', 255)->nullable();
            $table->json('screenshots')->nullable();
            $table->string('documentation_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->json('highlights')->nullable();         // ['✓ Unlimited funds', '✓ M-Pesa integration']
            
            // Installation Configuration
            $table->string('migration_path', 255)->nullable();
            $table->string('seeder_class', 255)->nullable();
            $table->json('install_hooks')->nullable();      // Pre/post install hooks
            $table->integer('estimated_install_time_seconds')->default(30);
            
            // Security & Approval
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_public')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_level', ['auto', 'admin', 'manual_review'])->default('auto');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('super_admins');
            $table->foreignId('updated_by')->nullable()->constrained('super_admins');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for Performance
            $table->index(['is_active', 'is_public', 'category'], 'idx_marketplace_browse');
            $table->index(['is_free', 'price_monthly'], 'idx_price_range');
            $table->fullText(['name', 'description', 'short_description'], 'idx_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
```

#### Migration: `plan_modules` (Plan-Module Matrix)

```php
<?php
// database/migrations/2026_03_28_000002_create_plan_modules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 50);
            
            // Inclusion & Availability
            $table->boolean('is_included')->default(false)->index();      // In plan price
            $table->boolean('is_available')->default(true);                // Can purchase add-on
            $table->boolean('is_featured')->default(false);                // Featured on plan page
            
            // Pricing Overrides (NULL = use module default)
            $table->decimal('price_monthly_override', 10, 2)->nullable();
            $table->decimal('price_yearly_override', 10, 2)->nullable();
            $table->decimal('setup_fee_override', 10, 2)->nullable();
            
            // Limits Override
            $table->json('limits_override')->nullable();
            
            // Trial Configuration
            $table->integer('trial_days')->default(0);                     // Override module default
            $table->boolean('extend_existing_trial')->default(false);      // Add to existing trial
            
            // Display Configuration
            $table->json('plan_highlights')->nullable();                   // Plan-specific highlights
            $table->string('plan_badge', 50)->nullable();                  // 'Popular', 'New', etc.
            
            // Configuration
            $table->json('configuration')->nullable();                     // Plan-specific module config
            
            $table->timestamps();
            
            // Constraints & Indexes
            $table->unique(['plan_id', 'module_key'], 'unique_plan_module');
            $table->index(['plan_id', 'is_included', 'is_available'], 'idx_plan_modules_lookup');
            
            // Foreign key to modules (soft reference for flexibility)
            // We use string key rather than foreignId for loose coupling
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_modules');
    }
};
```

#### Migration: `tenant_module_subscriptions` (Installation Tracking)

```php
<?php
// database/migrations/2026_03_28_000003_create_tenant_module_subscriptions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 50);
            
            // Installation Lifecycle Status
            $table->enum('status', [
                'pending',           // Queued for installation
                'installing',        // Currently installing
                'active',            // Fully operational
                'suspended',         // Payment issue or admin action
                'paused',            // Temporarily disabled
                'uninstalling',      // In progress
                'uninstalled',       // Cleaned up
                'failed',            // Installation failed
            ])->default('pending')->index();
            
            // Billing Information
            $table->enum('billing_type', [
                'plan_included',
                'addon_monthly',
                'addon_yearly',
                'one_time',
                'trial',
                'complimentary',
            ]);
            $table->decimal('price', 10, 2)->nullable();       // Price at subscription time
            $table->string('currency', 3)->default('KES');
            $table->string('idempotency_key', 100)->unique()->nullable(); // For payment safety
            
            // Dates
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            
            // Installation Tracking
            $table->foreignId('installed_by')->nullable()->constrained('users');
            $table->string('version_installed', 20)->nullable();
            $table->json('installation_log')->nullable();       // Step-by-step log
            $table->text('installation_error')->nullable();     // If failed
            
            // Settings & Configuration
            $table->json('settings')->nullable();               // Module-specific settings
            $table->json('limits')->nullable();                 // Applied limits
            $table->json('features_enabled')->nullable();       // Feature flags state
            
            // Usage Tracking
            $table->bigInteger('usage_count')->default(0);      // Generic usage counter
            $table->json('usage_metrics')->nullable();          // Module-specific metrics
            $table->timestamp('last_used_at')->nullable();
            
            // Cancellation Tracking
            $table->text('cancellation_reason')->nullable();
            $table->json('cancellation_feedback')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('super_admins');
            $table->foreignId('updated_by')->nullable()->constrained('super_admins');
            $table->timestamps();
            
            // Indexes for Common Queries
            $table->unique(['tenant_id', 'module_key'], 'unique_tenant_module');
            $table->index(['tenant_id', 'status'], 'idx_tenant_status');
            $table->index(['next_billing_at', 'status'], 'idx_billing_schedule');
            $table->index(['status', 'created_at'], 'idx_pending_installs');
            $table->index(['trial_ends_at'], 'idx_trial_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_module_subscriptions');
    }
};
```

#### Migration: `module_permissions` (Granular Permissions)

```php
<?php
// database/migrations/2026_03_28_000004_create_module_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('permission_key', 100);           // 'finance.view_reports'
            $table->string('name', 100);                      // Display name
            $table->text('description')->nullable();
            $table->enum('level', ['basic', 'advanced', 'premium'])->default('basic');
            $table->boolean('is_premium')->default(false);    // Requires premium tier
            $table->json('requires_features')->nullable();    // Feature flags needed
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['module_key', 'permission_key'], 'unique_module_perm');
            $table->index(['module_key', 'is_active'], 'idx_module_perms');
        });
        
        // Pivot table for tenant-specific permission grants
        Schema::create('tenant_module_permission_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_module_subscription_id')
                  ->constrained('tenant_module_subscriptions')
                  ->cascadeOnDelete();
            $table->foreignId('module_permission_id')
                  ->constrained('module_permissions')
                  ->cascadeOnDelete();
            $table->boolean('is_granted')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->json('conditions')->nullable();           // Conditional grants
            $table->timestamps();
            
            $table->unique(['tenant_module_subscription_id', 'module_permission_id'], 
                          'unique_permission_grant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_module_permission_grants');
        Schema::dropIfExists('module_permissions');
    }
};
```

#### Migration: `module_categories` & `module_dependencies`

```php
<?php
// database/migrations/2026_03_28_000005_create_module_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories for marketplace organization
        Schema::create('module_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Explicit dependency tracking
        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('depends_on_key', 50);
            $table->boolean('is_required')->default(true);     // Hard vs soft dependency
            $table->string('min_version', 20)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['module_key', 'depends_on_key'], 'unique_dependency');
            $table->index(['depends_on_key'], 'idx_dependents');
        });
        
        // Module changelog for versioning
        Schema::create('module_changelogs', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('version', 20);
            $table->enum('type', ['major', 'minor', 'patch', 'security']);
            $table->text('changelog');
            $table->json('breaking_changes')->nullable();
            $table->json('migration_required')->nullable();
            $table->timestamp('released_at');
            $table->timestamps();
            
            $table->unique(['module_key', 'version'], 'unique_version');
            $table->index(['module_key', 'released_at'], 'idx_changelog');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_changelogs');
        Schema::dropIfExists('module_dependencies');
        Schema::dropIfExists('module_categories');
    }
};
```

#### Migration: Update Existing Tables

```php
<?php
// database/migrations/2026_03_28_000006_update_plan_and_tenant_modules.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update plans table with marketplace mode
        Schema::table('plans', function (Blueprint $table) {
            $table->enum('module_mode', ['whitelist', 'blacklist', 'marketplace'])
                  ->default('whitelist')
                  ->after('features');
            $table->boolean('allow_addon_purchases')
                  ->default(false)
                  ->after('module_mode');
            $table->integer('max_addons')
                  ->default(0)
                  ->comment('0 = unlimited')
                  ->after('allow_addon_purchases');
            $table->boolean('allow_downgrades')
                  ->default(true)
                  ->after('max_addons');
            $table->json('marketplace_settings')
                  ->nullable()
                  ->comment('Plan-specific marketplace configuration')
                  ->after('allow_downgrades');
        });
        
        // Link tenant_modules to subscriptions
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->foreignId('subscription_id')
                  ->nullable()
                  ->after('module')
                  ->constrained('tenant_module_subscriptions')
                  ->nullOnDelete();
            $table->enum('installed_via', ['plan', 'marketplace', 'admin', 'migration'])
                  ->default('plan')
                  ->after('subscription_id');
            $table->string('source_subscription_key', 50)
                  ->nullable()
                  ->comment('Reference to original subscription for tracking')
                  ->after('installed_via');
            
            $table->index(['subscription_id'], 'idx_tenant_module_subscription');
            $table->index(['installed_via'], 'idx_installed_via');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'installed_via', 'source_subscription_key']);
        });
        
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'module_mode', 
                'allow_addon_purchases', 
                'max_addons',
                'allow_downgrades',
                'marketplace_settings'
            ]);
        });
    }
};
```

### 1.2 Data Seeder

```php
<?php
// database/seeders/ModuleRegistrySeeder.php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Plan;
use App\Models\PlanModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleRegistrySeeder extends Seeder
{
    /**
     * Seed the module registry with all available modules.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCategories();
            $modules = $this->seedModules();
            $this->seedPlanModules($modules);
            $this->migrateExistingModules();
        });
    }
    
    private function seedCategories(): void
    {
        $categories = [
            ['slug' => 'core', 'name' => 'Core Features', 'icon' => 'bi-box', 'sort_order' => 1],
            ['slug' => 'people', 'name' => 'People & Membership', 'icon' => 'bi-people', 'sort_order' => 2],
            ['slug' => 'finance', 'name' => 'Finance & Giving', 'icon' => 'bi-cash-stack', 'sort_order' => 3],
            ['slug' => 'communication', 'name' => 'Communication', 'icon' => 'bi-chat-dots', 'sort_order' => 4],
            ['slug' => 'engagement', 'name' => 'Engagement', 'icon' => 'bi-heart', 'sort_order' => 5],
            ['slug' => 'content', 'name' => 'Content Management', 'icon' => 'bi-file-text', 'sort_order' => 6],
            ['slug' => 'admin', 'name' => 'Administration', 'icon' => 'bi-gear', 'sort_order' => 7],
            ['slug' => 'premium', 'name' => 'Premium Features', 'icon' => 'bi-stars', 'sort_order' => 8],
        ];
        
        foreach ($categories as $category) {
            ModuleCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
    
    private function seedModules(): array
    {
        $modules = [
            // Core Modules (Always Free)
            [
                'key' => 'core',
                'name' => 'Core Platform',
                'category' => 'core',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Base platform functionality including authentication and user management',
                'short_description' => 'Essential platform features',
                'icon' => 'bi-box',
                'features' => ['auth', 'profiles', 'settings'],
                'dependencies' => [],
            ],
            [
                'key' => 'dashboard',
                'name' => 'Dashboard',
                'category' => 'core',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Main dashboard and navigation',
                'icon' => 'bi-speedometer2',
                'dependencies' => ['core'],
            ],
            
            // Standard Modules
            [
                'key' => 'people',
                'name' => 'People & Membership',
                'category' => 'people',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Complete member directory with profiles, groups, and relationships',
                'short_description' => 'Manage your congregation',
                'icon' => 'bi-people',
                'screenshots' => ['people-overview.jpg', 'member-profile.jpg'],
                'highlights' => ['✓ Unlimited members', '✓ Family relationships', '✓ Custom fields', '✓ Import/Export'],
                'features' => ['members', 'families', 'groups', 'children_checkin', 'duplicates'],
                'default_limits' => ['max_members' => null, 'max_groups' => null],
                'dependencies' => ['core'],
            ],
            [
                'key' => 'attendance',
                'name' => 'Attendance Tracking',
                'category' => 'people',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Track service attendance, events, and children check-in/check-out',
                'icon' => 'bi-clipboard-check',
                'features' => ['service_attendance', 'event_attendance', 'checkin', 'reports'],
                'dependencies' => ['people'],
            ],
            [
                'key' => 'finance',
                'name' => 'Finance & Giving',
                'category' => 'finance',
                'is_free' => false,
                'price_monthly' => 1500.00,
                'price_yearly' => 15000.00,
                'is_active' => true,
                'description' => 'Track tithes, offerings, manage funds, and generate financial reports',
                'short_description' => 'Complete church financial management',
                'icon' => 'bi-cash-stack',
                'highlights' => ['✓ Unlimited funds', '✓ Budget tracking', '✓ Financial reports', '✓ Multiple currencies'],
                'features' => ['funds', 'budgets', 'pledges', 'activities', 'reports', 'assets'],
                'default_limits' => ['max_fund_sources' => null, 'storage_mb' => 500],
                'dependencies' => ['people'],
                'estimated_install_time_seconds' => 60,
            ],
            [
                'key' => 'mpesa',
                'name' => 'M-Pesa Integration',
                'category' => 'finance',
                'is_free' => false,
                'price_monthly' => 1000.00,
                'price_yearly' => 10000.00,
                'setup_fee' => 500.00,
                'is_active' => true,
                'description' => 'Accept tithes and offerings automatically via M-Pesa',
                'short_description' => 'Mobile money payments',
                'icon' => 'bi-phone',
                'highlights' => ['✓ Auto-reconciliation', '✓ SMS notifications', '✓ Real-time reports', '✓ Multiple paybills'],
                'dependencies' => ['finance'],
            ],
            [
                'key' => 'sms',
                'name' => 'SMS Messaging',
                'category' => 'communication',
                'is_free' => false,
                'price_monthly' => 500.00,
                'price_yearly' => 5000.00,
                'billing_model' => 'usage_based',
                'is_active' => true,
                'description' => 'Send bulk SMS with scheduling, templates, and delivery tracking',
                'icon' => 'bi-chat-dots',
                'highlights' => ['✓ Bulk messaging', '✓ Scheduled sending', '✓ Templates', '✓ Delivery reports'],
                'dependencies' => ['people'],
            ],
            [
                'key' => 'email',
                'name' => 'Email Campaigns',
                'category' => 'communication',
                'is_free' => false,
                'price_monthly' => 800.00,
                'price_yearly' => 8000.00,
                'is_active' => true,
                'description' => 'Beautiful email newsletters with templates and analytics',
                'icon' => 'bi-envelope',
                'highlights' => ['✓ Drag & drop editor', '✓ Templates', '✓ Open tracking', '✓ Scheduling'],
                'dependencies' => ['people'],
            ],
            [
                'key' => 'events',
                'name' => 'Events & Notices',
                'category' => 'engagement',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Event management with registration and notice board',
                'icon' => 'bi-calendar-event',
                'dependencies' => ['core'],
            ],
            [
                'key' => 'spiritual',
                'name' => 'Spiritual Content',
                'category' => 'engagement',
                'is_free' => true,
                'is_active' => true,
                'description' => 'Sermons, testimonials, prayer requests, and discipleship tracking',
                'icon' => 'bi-book',
                'features' => ['sermons', 'testimonials', 'prayers', 'discipleship'],
                'dependencies' => ['core'],
            ],
            [
                'key' => 'discipleship',
                'name' => 'Discipleship & Mentorship',
                'category' => 'engagement',
                'is_free' => false,
                'price_monthly' => 750.00,
                'price_yearly' => 7500.00,
                'is_active' => true,
                'description' => 'Track discipleship journeys and mentorship relationships',
                'icon' => 'bi-heart',
                'dependencies' => ['spiritual', 'people'],
            ],
            [
                'key' => 'website',
                'name' => 'Church Website',
                'category' => 'content',
                'is_free' => false,
                'price_monthly' => 1200.00,
                'price_yearly' => 12000.00,
                'is_active' => true,
                'description' => 'Public-facing website with customization and custom domains',
                'icon' => 'bi-globe',
                'features' => ['homepage', 'blog', 'gallery', 'custom_domain'],
                'dependencies' => ['core'],
            ],
            [
                'key' => 'shop',
                'name' => 'Shop & E-commerce',
                'category' => 'finance',
                'is_free' => false,
                'price_monthly' => 2000.00,
                'price_yearly' => 20000.00,
                'setup_fee' => 1000.00,
                'is_active' => true,
                'description' => 'Sell products, books, and event tickets online',
                'icon' => 'bi-shop',
                'dependencies' => ['core'],
            ],
            [
                'key' => 'media',
                'name' => 'Media Library',
                'category' => 'content',
                'is_free' => false,
                'price_monthly' => 600.00,
                'price_yearly' => 6000.00,
                'is_active' => true,
                'description' => 'File and media storage with folder management',
                'icon' => 'bi-images',
                'features' => ['folders', 'uploads', 'organization'],
                'default_limits' => ['storage_mb' => 1024],
                'dependencies' => ['core'],
            ],
            [
                'key' => 'reports',
                'name' => 'Advanced Reports',
                'category' => 'admin',
                'is_free' => false,
                'price_monthly' => 900.00,
                'price_yearly' => 9000.00,
                'is_active' => true,
                'description' => 'Financial, attendance, and giving analytics',
                'icon' => 'bi-graph-up',
                'features' => ['financial', 'attendance', 'giving', 'custom'],
                'dependencies' => [],
            ],
            [
                'key' => 'links',
                'name' => 'Link Shortener',
                'category' => 'admin',
                'is_free' => false,
                'price_monthly' => 300.00,
                'price_yearly' => 3000.00,
                'is_active' => true,
                'description' => 'URL shortening with click tracking',
                'icon' => 'bi-link-45deg',
                'dependencies' => ['core'],
            ],
            
            // Premium Modules
            [
                'key' => 'api_access',
                'name' => 'API Access',
                'category' => 'premium',
                'is_free' => false,
                'price_monthly' => 3000.00,
                'price_yearly' => 30000.00,
                'setup_fee' => 2000.00,
                'is_active' => true,
                'description' => 'RESTful API with key management and webhooks',
                'icon' => 'bi-code',
                'features' => ['rest_api', 'webhooks', 'rate_limiting', 'docs'],
                'default_limits' => ['requests_per_hour' => 1000],
                'dependencies' => ['core'],
            ],
            [
                'key' => 'integrations',
                'name' => 'Third-Party Integrations',
                'category' => 'premium',
                'is_free' => false,
                'price_monthly' => 1500.00,
                'price_yearly' => 15000.00,
                'is_active' => true,
                'description' => 'Connect with external services and platforms',
                'icon' => 'bi-plug',
                'features' => ['zapier', 'webhooks', 'custom_integrations'],
                'dependencies' => ['core'],
            ],
        ];
        
        $createdModules = [];
        foreach ($modules as $moduleData) {
            $module = Module::firstOrCreate(
                ['key' => $moduleData['key']],
                array_merge($moduleData, ['created_by' => 1])
            );
            $createdModules[$module->key] = $module;
        }
        
        return $createdModules;
    }
    
    private function seedPlanModules(array $modules): void
    {
        $planModuleConfigs = [
            'free' => [
                'core' => ['is_included' => true],
                'dashboard' => ['is_included' => true],
                'people' => ['is_included' => true],
                'attendance' => ['is_included' => true],
                'events' => ['is_included' => true],
                'spiritual' => ['is_included' => true],
                'finance' => ['is_included' => false, 'is_available' => false],
                'mpesa' => ['is_included' => false, 'is_available' => false],
                'sms' => ['is_included' => false, 'is_available' => false],
                'email' => ['is_included' => false, 'is_available' => false],
                'website' => ['is_included' => false, 'is_available' => false],
                'shop' => ['is_included' => false, 'is_available' => false],
                'media' => ['is_included' => false, 'is_available' => false],
                'reports' => ['is_included' => false, 'is_available' => false],
                'discipleship' => ['is_included' => false, 'is_available' => false],
                'links' => ['is_included' => false, 'is_available' => false],
                'api_access' => ['is_included' => false, 'is_available' => false],
                'integrations' => ['is_included' => false, 'is_available' => false],
            ],
            'starter' => [
                'core' => ['is_included' => true],
                'dashboard' => ['is_included' => true],
                'people' => ['is_included' => true],
                'attendance' => ['is_included' => true],
                'events' => ['is_included' => true],
                'spiritual' => ['is_included' => true],
                'finance' => ['is_included' => true],
                'mpesa' => ['is_included' => false, 'is_available' => true],
                'sms' => ['is_included' => true, 'limits_override' => ['max_sms_per_month' => 500]],
                'email' => ['is_included' => true, 'limits_override' => ['max_emails_per_month' => 1000]],
                'website' => ['is_included' => true],
                'shop' => ['is_included' => false, 'is_available' => true],
                'media' => ['is_included' => true, 'limits_override' => ['storage_mb' => 1024]],
                'reports' => ['is_included' => true, 'features_enabled' => ['basic' => true, 'advanced' => false]],
                'discipleship' => ['is_included' => false, 'is_available' => true],
                'links' => ['is_included' => false, 'is_available' => true],
                'api_access' => ['is_included' => false, 'is_available' => false],
                'integrations' => ['is_included' => false, 'is_available' => false],
            ],
            'pro' => [
                'core' => ['is_included' => true],
                'dashboard' => ['is_included' => true],
                'people' => ['is_included' => true],
                'attendance' => ['is_included' => true],
                'events' => ['is_included' => true],
                'spiritual' => ['is_included' => true],
                'finance' => ['is_included' => true],
                'mpesa' => ['is_included' => true],
                'sms' => ['is_included' => true, 'limits_override' => ['max_sms_per_month' => 2000]],
                'email' => ['is_included' => true, 'limits_override' => ['max_emails_per_month' => 5000]],
                'website' => ['is_included' => true],
                'shop' => ['is_included' => true],
                'media' => ['is_included' => true, 'limits_override' => ['storage_mb' => 5120]],
                'reports' => ['is_included' => true, 'features_enabled' => ['basic' => true, 'advanced' => true]],
                'discipleship' => ['is_included' => true],
                'links' => ['is_included' => true],
                'api_access' => ['is_included' => false, 'is_available' => true],
                'integrations' => ['is_included' => false, 'is_available' => true],
            ],
            'enterprise' => [
                'core' => ['is_included' => true],
                'dashboard' => ['is_included' => true],
                'people' => ['is_included' => true],
                'attendance' => ['is_included' => true],
                'events' => ['is_included' => true],
                'spiritual' => ['is_included' => true],
                'finance' => ['is_included' => true],
                'mpesa' => ['is_included' => true],
                'sms' => ['is_included' => true],
                'email' => ['is_included' => true],
                'website' => ['is_included' => true],
                'shop' => ['is_included' => true],
                'media' => ['is_included' => true],
                'reports' => ['is_included' => true],
                'discipleship' => ['is_included' => true],
                'links' => ['is_included' => true],
                'api_access' => ['is_included' => true],
                'integrations' => ['is_included' => true],
            ],
        ];
        
        foreach ($planModuleConfigs as $planSlug => $moduleConfigs) {
            $plan = Plan::where('slug', $planSlug)->first();
            if (!$plan) continue;
            
            foreach ($moduleConfigs as $moduleKey => $config) {
                PlanModule::firstOrCreate(
                    ['plan_id' => $plan->id, 'module_key' => $moduleKey],
                    $config
                );
            }
        }
    }
    
    private function migrateExistingModules(): void
    {
        // Migrate existing tenant_modules to new subscription-based structure
        $existingModules = DB::table('tenant_modules')->get();
        
        foreach ($existingModules as $tm) {
            // Check if subscription already exists
            $exists = DB::table('tenant_module_subscriptions')
                ->where('tenant_id', $tm->tenant_id)
                ->where('module_key', $tm->module)
                ->exists();
            
            if (!$exists) {
                DB::table('tenant_module_subscriptions')->insert([
                    'tenant_id' => $tm->tenant_id,
                    'module_key' => $tm->module,
                    'status' => $tm->is_enabled ? 'active' : 'uninstalled',
                    'billing_type' => 'plan_included',
                    'installed_at' => $tm->enabled_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Update tenant_modules with subscription reference
                $subscriptionId = DB::table('tenant_module_subscriptions')
                    ->where('tenant_id', $tm->tenant_id)
                    ->where('module_key', $tm->module)
                    ->value('id');
                
                DB::table('tenant_modules')
                    ->where('id', $tm->id)
                    ->update([
                        'subscription_id' => $subscriptionId,
                        'installed_via' => $tm->override_by_admin ? 'admin' : 'plan',
                    ]);
            }
        }
    }
}
```

---

## Phase 2: Domain Layer & Business Logic

### 2.1 Domain Models

```php
<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key', 'name', 'slug', 'description', 'short_description',
        'category', 'tags', 'version', 'min_platform_version', 'max_platform_version',
        'dependencies', 'conflicts', 'is_free', 'price_monthly', 'price_yearly',
        'setup_fee', 'billing_model', 'features', 'default_limits',
        'icon', 'screenshots', 'documentation_url', 'video_url', 'highlights',
        'migration_path', 'seeder_class', 'install_hooks', 'estimated_install_time_seconds',
        'is_active', 'is_public', 'requires_approval', 'approval_level',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'dependencies' => 'array',
        'conflicts' => 'array',
        'is_free' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'features' => 'array',
        'default_limits' => 'array',
        'screenshots' => 'array',
        'highlights' => 'array',
        'install_hooks' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForMarketplace($query)
    {
        return $query->active()->public();
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    // Relationships
    public function planModules(): HasMany
    {
        return $this->hasMany(PlanModule::class, 'module_key', 'key');
    }

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantModuleSubscription::class, 'module_key', 'key');
    }

    public function changelogs(): HasMany
    {
        return $this->hasMany(ModuleChangelog::class, 'module_key', 'key');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'module_key', 'key');
    }

    // Helpers
    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function hasDependency(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->dependencies ?? []);
    }

    public function hasConflict(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->conflicts ?? []);
    }

    public function getPrice(string $billingCycle = 'monthly'): ?float
    {
        return $billingCycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    public function getYearlySavingsPercent(): ?int
    {
        if (!$this->price_monthly || !$this->price_yearly) {
            return null;
        }
        
        $monthlyCost = $this->price_monthly * 12;
        $savings = $monthlyCost - $this->price_yearly;
        
        return (int) round(($savings / $monthlyCost) * 100);
    }

    public function isCore(): bool
    {
        return $this->category === 'core';
    }

    public function requiresSetup(): bool
    {
        return !empty($this->migration_path) || !empty($this->seeder_class);
    }

    public function getInstallTimeEstimate(): string
    {
        $seconds = $this->estimated_install_time_seconds ?? 30;
        
        if ($seconds < 60) {
            return "{$seconds} seconds";
        }
        
        $minutes = ceil($seconds / 60);
        return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
    }
}
```

```php
<?php
// app/Models/PlanModule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id', 'module_key', 'is_included', 'is_available', 'is_featured',
        'price_monthly_override', 'price_yearly_override', 'setup_fee_override',
        'limits_override', 'trial_days', 'extend_existing_trial',
        'plan_highlights', 'plan_badge', 'configuration',
    ];

    protected $casts = [
        'is_included' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'price_monthly_override' => 'decimal:2',
        'price_yearly_override' => 'decimal:2',
        'setup_fee_override' => 'decimal:2',
        'limits_override' => 'array',
        'trial_days' => 'integer',
        'extend_existing_trial' => 'boolean',
        'plan_highlights' => 'array',
        'configuration' => 'array',
    ];

    // Relationships
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    // Helpers
    public function getPrice(string $billingCycle = 'monthly'): ?float
    {
        $override = $billingCycle === 'yearly' 
            ? $this->price_yearly_override 
            : $this->price_monthly_override;
        
        if ($override !== null) {
            return $override;
        }
        
        return $this->module?->getPrice($billingCycle);
    }

    public function getSetupFee(): float
    {
        return $this->setup_fee_override ?? $this->module?->setup_fee ?? 0;
    }

    public function getLimits(): array
    {
        $moduleLimits = $this->module?->default_limits ?? [];
        $overrideLimits = $this->limits_override ?? [];
        
        return array_merge($moduleLimits, $overrideLimits);
    }

    public function getTrialDays(): int
    {
        return $this->trial_days ?? $this->module?->trial_days ?? 0;
    }

    public function isAccessible(): bool
    {
        return $this->is_included || $this->is_available;
    }

    public function getEffectiveMonthlyPrice(): ?float
    {
        if ($this->is_included) {
            return 0;
        }
        
        return $this->getPrice('monthly');
    }
}
```

```php
<?php
// app/Models/TenantModuleSubscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantModuleSubscription extends Model
{
    use HasFactory;

    protected $table = 'tenant_module_subscriptions';

    protected $fillable = [
        'tenant_id', 'module_key', 'status', 'billing_type', 'price', 'currency',
        'idempotency_key', 'installed_at', 'trial_ends_at', 'next_billing_at',
        'last_billed_at', 'unsubscribed_at', 'suspended_at', 'suspension_reason',
        'installed_by', 'version_installed', 'installation_log', 'installation_error',
        'settings', 'limits', 'features_enabled', 'usage_count', 'usage_metrics',
        'last_used_at', 'cancellation_reason', 'cancellation_feedback', 'cancelled_by',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'installed_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'last_billed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'installation_log' => 'array',
        'settings' => 'array',
        'limits' => 'array',
        'features_enabled' => 'array',
        'usage_metrics' => 'array',
        'last_used_at' => 'datetime',
        'cancellation_feedback' => 'array',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInstalled($query)
    {
        return $query->whereIn('status', ['active', 'suspended', 'paused']);
    }

    public function scopePendingBilling($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now());
    }

    public function scopeInTrial($query)
    {
        return $query->where('billing_type', 'trial')
            ->where('trial_ends_at', '>', now());
    }

    public function scopeTrialExpired($query)
    {
        return $query->where('billing_type', 'trial')
            ->where('trial_ends_at', '<=', now());
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    public function tenantModule(): HasOne
    {
        return $this->hasOne(TenantModule::class, 'subscription_id');
    }

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    // Status Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInstalled(): bool
    {
        return in_array($this->status, ['active', 'suspended', 'paused']);
    }

    public function isInTrial(): bool
    {
        return $this->billing_type === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->billing_type === 'trial' && $this->trial_ends_at?->isPast();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRecurring(): bool
    {
        return in_array($this->billing_type, ['addon_monthly', 'addon_yearly']);
    }

    // Trial Management
    public function daysRemainingInTrial(): ?int
    {
        if (!$this->isInTrial()) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function trialProgressPercent(): int
    {
        if (!$this->isInTrial()) {
            return 100;
        }
        
        $totalDays = $this->module?->trial_days ?? 14;
        $remaining = $this->daysRemainingInTrial();
        
        return (int) round((($totalDays - $remaining) / $totalDays) * 100);
    }

    // Billing Helpers
    public function getBillingPeriodLabel(): string
    {
        return match($this->billing_type) {
            'plan_included' => 'Included in Plan',
            'addon_monthly' => 'Monthly',
            'addon_yearly' => 'Yearly',
            'one_time' => 'One-time Purchase',
            'trial' => 'Trial',
            'complimentary' => 'Complimentary',
            default => ucfirst(str_replace('_', ' ', $this->billing_type)),
        };
    }

    public function getNextBillingAmount(): ?float
    {
        if (!$this->isRecurring()) {
            return null;
        }
        
        return $this->price;
    }

    public function recordUsage(string $metric, int $increment = 1): void
    {
        $this->increment('usage_count', $increment);
        
        $metrics = $this->usage_metrics ?? [];
        $metrics[$metric] = ($metrics[$metric] ?? 0) + $increment;
        
        $this->update([
            'usage_metrics' => $metrics,
            'last_used_at' => now(),
        ]);
    }

    // Installation Log
    public function logInstallationStep(string $step, string $status, ?string $message = null): void
    {
        $log = $this->installation_log ?? [];
        $log[] = [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->update(['installation_log' => $log]);
    }

    public function getInstallationProgress(): int
    {
        if ($this->status === 'active') {
            return 100;
        }
        
        if ($this->status === 'failed') {
            return 0;
        }
        
        $log = $this->installation_log ?? [];
        $totalSteps = 5; // Discover, Validate, Migrate, Seed, Activate
        $completedSteps = count(array_filter($log, fn($l) => ($l['status'] ?? '') === 'complete'));
        
        return (int) round(($completedSteps / $totalSteps) * 100);
    }
}
```

### 2.2 Repository Pattern Implementation

```php
<?php
// app/Repositories/Contracts/ModuleRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Module;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModuleRepositoryInterface
{
    /**
     * Find module by key.
     */
    public function findByKey(string $key): ?Module;

    /**
     * Get all active modules for marketplace.
     */
    public function getMarketplaceModules(): Collection;

    /**
     * Get modules available to a specific tenant.
     */
    public function getAvailableForTenant(Tenant $tenant): Collection;

    /**
     * Get modules with their plan-specific configuration.
     */
    public function getForPlan(int $planId): Collection;

    /**
     * Search modules with filters.
     */
    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Check if module can be installed for tenant.
     */
    public function canInstall(string $moduleKey, Tenant $tenant): bool;

    /**
     * Get module dependencies with status for tenant.
     */
    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array;
}
```

```php
<?php
// app/Repositories/ModuleRepository.php

namespace App\Repositories;

use App\Models\Module;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function findByKey(string $key): ?Module
    {
        return Module::where('key', $key)->first();
    }

    public function getMarketplaceModules(): Collection
    {
        return Module::forMarketplace()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getAvailableForTenant(Tenant $tenant): Collection
    {
        $plan = $tenant->plan;
        
        if (!$plan) {
            // Free tier - only core modules
            return Module::forMarketplace()
                ->whereIn('category', ['core', 'people', 'engagement'])
                ->get();
        }
        
        // Get plan modules
        $planModuleKeys = $plan->planModules()
            ->where(function ($q) {
                $q->where('is_included', true)
                  ->orWhere('is_available', true);
            })
            ->pluck('module_key');
        
        return Module::forMarketplace()
            ->whereIn('key', $planModuleKeys)
            ->orderBy('sort_order')
            ->get();
    }

    public function getForPlan(int $planId): Collection
    {
        return Module::whereHas('planModules', function ($q) use ($planId) {
            $q->where('plan_id', $planId);
        })
        ->with(['planModules' => function ($q) use ($planId) {
            $q->where('plan_id', $planId);
        }])
        ->orderBy('category')
        ->orderBy('sort_order')
        ->get();
    }

    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Module::forMarketplace();
        
        if (!empty($filters['category'])) {
            $query->inCategory($filters['category']);
        }
        
        if (!empty($filters['price_type'])) {
            match($filters['price_type']) {
                'free' => $query->free(),
                'paid' => $query->paid(),
                default => null,
            };
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }
        
        if (!empty($filters['has_trial'])) {
            $query->where('trial_days', '>', 0);
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        
        $allowedSorts = ['name', 'price_monthly', 'sort_order', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        return $query->paginate($perPage);
    }

    public function canInstall(string $moduleKey, Tenant $tenant): bool
    {
        // Check if already installed
        $existing = DB::table('tenant_module_subscriptions')
            ->where('tenant_id', $tenant->id)
            ->where('module_key', $moduleKey)
            ->whereIn('status', ['active', 'pending', 'installing'])
            ->exists();
        
        if ($existing) {
            return false;
        }
        
        $module = $this->findByKey($moduleKey);
        if (!$module || !$module->is_active) {
            return false;
        }
        
        // Check plan allows
        if (!$this->planAllowsInstallation($moduleKey, $tenant)) {
            return false;
        }
        
        // Check dependencies
        $deps = $this->getDependencyStatus($moduleKey, $tenant);
        foreach ($deps as $dep) {
            if ($dep['required'] && !$dep['installed']) {
                return false;
            }
        }
        
        // Check conflicts
        foreach ($module->conflicts ?? [] as $conflict) {
            if ($tenant->hasModule($conflict)) {
                return false;
            }
        }
        
        return true;
    }

    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array
    {
        $module = $this->findByKey($moduleKey);
        if (!$module) {
            return [];
        }
        
        $dependencies = [];
        
        foreach ($module->dependencies ?? [] as $depKey) {
            $depModule = $this->findByKey($depKey);
            $installed = $tenant->hasModule($depKey);
            
            $dependencies[] = [
                'key' => $depKey,
                'name' => $depModule?->name ?? $depKey,
                'required' => true,
                'installed' => $installed,
                'can_install' => !$installed && $this->canInstall($depKey, $tenant),
            ];
        }
        
        return $dependencies;
    }

    private function planAllowsInstallation(string $moduleKey, Tenant $tenant): bool
    {
        $plan = $tenant->plan;
        
        if (!$plan) {
            return false;
        }
        
        // Check module_mode
        if ($plan->module_mode === 'whitelist') {
            return $plan->planModules()
                ->where('module_key', $moduleKey)
                ->where('is_included', true)
                ->exists();
        }
        
        if ($plan->module_mode === 'blacklist') {
            $excluded = $plan->planModules()
                ->where('module_key', $moduleKey)
                ->where('is_available', false)
                ->exists();
            return !$excluded;
        }
        
        // marketplace mode
        if (!$plan->allow_addon_purchases) {
            return false;
        }
        
        // Check max_addons limit
        if ($plan->max_addons > 0) {
            $currentAddons = $tenant->modules()
                ->where('installed_via', 'marketplace')
                ->count();
            
            if ($currentAddons >= $plan->max_addons) {
                return false;
            }
        }
        
        return $plan->planModules()
            ->where('module_key', $moduleKey)
            ->where('is_available', true)
            ->exists();
    }
}
```

```php
<?php
// app/Repositories/CachedModuleRepository.php

namespace App\Repositories;

use App\Models\Module;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Decorator pattern for caching module repository results.
 */
class CachedModuleRepository implements ModuleRepositoryInterface
{
    private ModuleRepositoryInterface $repository;
    private int $ttl;

    public function __construct(ModuleRepositoryInterface $repository, int $ttlMinutes = 5)
    {
        $this->repository = $repository;
        $this->ttl = $ttlMinutes * 60;
    }

    public function findByKey(string $key): ?Module
    {
        return Cache::remember(
            "module:{$key}",
            $this->ttl,
            fn() => $this->repository->findByKey($key)
        );
    }

    public function getMarketplaceModules(): Collection
    {
        return Cache::remember(
            'modules:marketplace',
            $this->ttl,
            fn() => $this->repository->getMarketplaceModules()
        );
    }

    public function getAvailableForTenant(Tenant $tenant): Collection
    {
        return Cache::remember(
            "tenant:{$tenant->id}:available_modules",
            $this->ttl,
            fn() => $this->repository->getAvailableForTenant($tenant)
        );
    }

    public function getForPlan(int $planId): Collection
    {
        return Cache::remember(
            "plan:{$planId}:modules",
            $this->ttl,
            fn() => $this->repository->getForPlan($planId)
        );
    }

    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        // Don't cache search results - too variable
        return $this->repository->search($filters, $perPage);
    }

    public function canInstall(string $moduleKey, Tenant $tenant): bool
    {
        // Short TTL for this - changes frequently
        return Cache::remember(
            "tenant:{$tenant->id}:can_install:{$moduleKey}",
            60, // 1 minute
            fn() => $this->repository->canInstall($moduleKey, $tenant)
        );
    }

    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array
    {
        return Cache::remember(
            "tenant:{$tenant->id}:deps:{$moduleKey}",
            $this->ttl,
            fn() => $this->repository->getDependencyStatus($moduleKey, $tenant)
        );
    }

    // Cache invalidation helpers
    public function invalidateModule(string $key): void
    {
        Cache::forget("module:{$key}");
        Cache::forget('modules:marketplace');
    }

    public function invalidateTenant(int $tenantId): void
    {
        Cache::forget("tenant:{$tenantId}:available_modules");
        Cache::forget("tenant:{$tenantId}:modules");
    }

    public function invalidatePlan(int $planId): void
    {
        Cache::forget("plan:{$planId}:modules");
    }
}
```

### 2.3 Value Objects & DTOs

```php
<?php
// app/DTOs/ModuleInstallRequest.php

namespace App\DTOs;

use App\Models\Tenant;
use App\Models\User;

readonly class ModuleInstallRequest
{
    public function __construct(
        public string $moduleKey,
        public Tenant $tenant,
        public User $requestedBy,
        public string $billingCycle = 'monthly',
        public ?string $idempotencyKey = null,
        public bool $autoApprove = false,
        public ?array $settings = null,
        public ?int $trialDays = null,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey ?? $this->generateIdempotencyKey();
    }

    private function generateIdempotencyKey(): string
    {
        return sprintf(
            'install:%d:%s:%s:%s',
            $this->tenant->id,
            $this->moduleKey,
            $this->billingCycle,
            now()->format('Y-m-d-H')
        );
    }
}
```

```php
<?php
// app/DTOs/ModulePrice.php

namespace App\DTOs;

readonly class ModulePrice
{
    public function __construct(
        public ?float $monthly = null,
        public ?float $yearly = null,
        public float $setupFee = 0,
        public ?int $yearlySavingsPercent = null,
        public string $currency = 'KES',
    ) {}

    public function getPrice(string $billingCycle): ?float
    {
        return $billingCycle === 'yearly' ? $this->yearly : $this->monthly;
    }

    public function isFree(): bool
    {
        return $this->monthly === null && $this->yearly === null && $this->setupFee === 0;
    }

    public function format(string $billingCycle): string
    {
        $price = $this->getPrice($billingCycle);
        
        if ($price === null) {
            return 'Free';
        }
        
        return sprintf('%s %s', $this->currency, number_format($price, 2));
    }

    public function toArray(): array
    {
        return [
            'monthly' => $this->monthly,
            'yearly' => $this->yearly,
            'setup_fee' => $this->setupFee,
            'yearly_savings_percent' => $this->yearlySavingsPercent,
            'currency' => $this->currency,
            'is_free' => $this->isFree(),
        ];
    }
}
```

```php
<?php
// app/DTOs/InstallationResult.php

namespace App\DTOs;

use App\Models\TenantModuleSubscription;

readonly class InstallationResult
{
    public function __construct(
        public bool $success,
        public TenantModuleSubscription $subscription,
        public array $steps = [],
        public ?string $error = null,
        public ?string $redirectUrl = null,
    ) {}

    public static function success(
        TenantModuleSubscription $subscription,
        array $steps = [],
        ?string $redirectUrl = null
    ): self {
        return new self(
            success: true,
            subscription: $subscription,
            steps: $steps,
            redirectUrl: $redirectUrl
        );
    }

    public static function failure(
        TenantModuleSubscription $subscription,
        string $error,
        array $steps = []
    ): self {
        return new self(
            success: false,
            subscription: $subscription,
            steps: $steps,
            error: $error
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'subscription_id' => $this->subscription->id,
            'status' => $this->subscription->status,
            'steps' => $this->steps,
            'error' => $this->error,
            'redirect_url' => $this->redirectUrl,
        ];
    }
}
```

---

## Phase 3: Marketplace Services

### 3.1 Module Installation Service

```php
<?php
// app/Services/Modules/ModuleInstaller.php

namespace App\Services\Modules;

use App\DTOs\InstallationResult;
use App\DTOs\ModuleInstallRequest;
use App\Events\ModuleInstallationFailed;
use App\Events\ModuleInstalled;
use App\Events\ModuleInstallationStarted;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\TenantModuleSubscription;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ModuleInstaller
{
    private ModuleRepositoryInterface $moduleRepository;
    private PricingCalculator $pricingCalculator;
    private DependencyResolver $dependencyResolver;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        PricingCalculator $pricingCalculator,
        DependencyResolver $dependencyResolver
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->pricingCalculator = $pricingCalculator;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Install a module for a tenant.
     *
     * @throws ModuleInstallationException
     */
    public function install(ModuleInstallRequest $request): InstallationResult
    {
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        
        if (!$module) {
            throw new ModuleInstallationException("Module {$request->moduleKey} not found");
        }

        // Check idempotency
        if ($this->isDuplicateRequest($request)) {
            Log::info('Duplicate module installation request detected', [
                'tenant_id' => $request->tenant->id,
                'module' => $request->moduleKey,
                'idempotency_key' => $request->getIdempotencyKey(),
            ]);
            
            $existing = $this->findExistingSubscription($request);
            return InstallationResult::success($existing);
        }

        // Validate installation
        if (!$this->moduleRepository->canInstall($request->moduleKey, $request->tenant)) {
            throw new ModuleInstallationException('Module cannot be installed for this tenant');
        }

        return DB::transaction(function () use ($request, $module) {
            // Create subscription record
            $subscription = $this->createSubscription($request);
            
            // Dispatch event
            event(new ModuleInstallationStarted($module, $request->tenant, $subscription));
            
            try {
                // Execute installation steps
                $steps = $this->executeInstallation($subscription, $request);
                
                // Activate module
                $this->activateModule($subscription, $request->tenant);
                
                // Update subscription
                $subscription->update([
                    'status' => 'active',
                    'installed_at' => now(),
                    'version_installed' => $module->version,
                ]);
                
                // Dispatch success event
                event(new ModuleInstalled($module, $request->tenant, $subscription, $request->requestedBy));
                
                return InstallationResult::success($subscription, $steps);
                
            } catch (Throwable $e) {
                return $this->handleInstallationFailure($subscription, $e, $request);
            }
        });
    }

    /**
     * Install with automatic dependency resolution.
     */
    public function installWithDependencies(ModuleInstallRequest $request): array
    {
        $dependencies = $this->dependencyResolver->resolve($request->moduleKey, $request->tenant);
        $results = [];
        
        // Install dependencies first
        foreach ($dependencies as $dep) {
            if (!$dep['installed'] && $dep['required']) {
                $depRequest = new ModuleInstallRequest(
                    moduleKey: $dep['key'],
                    tenant: $request->tenant,
                    requestedBy: $request->requestedBy,
                    billingCycle: $request->billingCycle,
                    autoApprove: true, // Dependencies auto-approved
                );
                
                $results[$dep['key']] = $this->install($depRequest);
            }
        }
        
        // Install main module
        $results[$request->moduleKey] = $this->install($request);
        
        return $results;
    }

    /**
     * Queue installation for background processing.
     */
    public function queueInstall(ModuleInstallRequest $request): TenantModuleSubscription
    {
        $subscription = $this->createSubscription($request);
        
        // Dispatch to queue
        \App\Jobs\ModuleInstallationJob::dispatch($subscription, $request)
            ->onQueue('module-installations');
        
        return $subscription;
    }

    /**
     * Uninstall a module.
     */
    public function uninstall(TenantModuleSubscription $subscription, ?string $reason = null, bool $purgeData = false): bool
    {
        $module = $this->moduleRepository->findByKey($subscription->module_key);
        
        return DB::transaction(function () use ($subscription, $module, $reason, $purgeData) {
            // Check for dependents
            $dependents = $this->dependencyResolver->getDependents(
                $subscription->module_key, 
                $subscription->tenant
            );
            
            if (!empty($dependents)) {
                throw new ModuleInstallationException(
                    'Cannot uninstall: other modules depend on this one'
                );
            }
            
            $subscription->update([
                'status' => 'uninstalling',
                'cancellation_reason' => $reason,
                'cancelled_by' => auth()->id(),
            ]);
            
            try {
                if ($purgeData) {
                    $this->purgeModuleData($subscription);
                }
                
                // Disable in tenant_modules
                TenantModule::where('tenant_id', $subscription->tenant_id)
                    ->where('module', $subscription->module_key)
                    ->update([
                        'is_enabled' => false,
                        'disabled_at' => now(),
                    ]);
                
                $subscription->update([
                    'status' => 'uninstalled',
                    'unsubscribed_at' => now(),
                ]);
                
                event(new \App\Events\ModuleUninstalled($module, $subscription->tenant, $subscription));
                
                return true;
                
            } catch (Throwable $e) {
                $subscription->update([
                    'status' => 'failed',
                    'installation_error' => $e->getMessage(),
                ]);
                
                throw $e;
            }
        });
    }

    // Private methods...

    private function createSubscription(ModuleInstallRequest $request): TenantModuleSubscription
    {
        $price = $this->pricingCalculator->calculate($request->moduleKey, $request->tenant, $request->billingCycle);
        
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        
        $trialDays = $request->trialDays ?? $this->getTrialDays($request->moduleKey, $request->tenant);
        
        return TenantModuleSubscription::create([
            'tenant_id' => $request->tenant->id,
            'module_key' => $request->moduleKey,
            'status' => 'pending',
            'billing_type' => $trialDays > 0 ? 'trial' : ($request->billingCycle === 'yearly' ? 'addon_yearly' : 'addon_monthly'),
            'price' => $price->getPrice($request->billingCycle),
            'currency' => $price->currency,
            'idempotency_key' => $request->getIdempotencyKey(),
            'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
            'next_billing_at' => $trialDays > 0 ? null : $this->calculateNextBilling($request->billingCycle),
            'settings' => $request->settings,
            'installed_by' => $request->requestedBy->id,
            'created_by' => auth('superadmin')->id(),
        ]);
    }

    private function executeInstallation(TenantModuleSubscription $subscription, ModuleInstallRequest $request): array
    {
        $module = $this->moduleRepository->findByKey($subscription->module_key);
        $steps = [];
        
        // Step 1: Pre-install hooks
        $subscription->logInstallationStep('pre_install', 'running');
        $this->runPreInstallHooks($module, $request->tenant);
        $subscription->logInstallationStep('pre_install', 'complete');
        $steps[] = ['step' => 'pre_install', 'status' => 'complete'];
        
        // Step 2: Run migrations
        if ($module->migration_path) {
            $subscription->logInstallationStep('migrations', 'running');
            $this->runMigrations($module, $request->tenant);
            $subscription->logInstallationStep('migrations', 'complete');
            $steps[] = ['step' => 'migrations', 'status' => 'complete'];
        }
        
        // Step 3: Run seeders
        if ($module->seeder_class) {
            $subscription->logInstallationStep('seeders', 'running');
            $this->runSeeders($module, $request->tenant);
            $subscription->logInstallationStep('seeders', 'complete');
            $steps[] = ['step' => 'seeders', 'status' => 'complete'];
        }
        
        // Step 4: Post-install hooks
        $subscription->logInstallationStep('post_install', 'running');
        $this->runPostInstallHooks($module, $request->tenant);
        $subscription->logInstallationStep('post_install', 'complete');
        $steps[] = ['step' => 'post_install', 'status' => 'complete'];
        
        return $steps;
    }

    private function activateModule(TenantModuleSubscription $subscription, Tenant $tenant): void
    {
        TenantModule::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'module' => $subscription->module_key,
            ],
            [
                'is_enabled' => true,
                'subscription_id' => $subscription->id,
                'installed_via' => $subscription->billing_type === 'plan_included' ? 'plan' : 'marketplace',
                'enabled_at' => now(),
                'disabled_at' => null,
            ]
        );
        
        // Clear cache
        app(\App\Services\ModuleService::class)->flushCache($tenant->id, $subscription->module_key);
    }

    private function handleInstallationFailure(
        TenantModuleSubscription $subscription, 
        Throwable $e,
        ModuleInstallRequest $request
    ): InstallationResult {
        Log::error('Module installation failed', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $request->tenant->id,
            'module' => $request->moduleKey,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $subscription->update([
            'status' => 'failed',
            'installation_error' => $e->getMessage(),
        ]);
        
        $module = $this->moduleRepository->findByKey($request->moduleKey);
        event(new ModuleInstallationFailed($module, $request->tenant, $subscription, $e));
        
        return InstallationResult::failure($subscription, $e->getMessage());
    }

    private function isDuplicateRequest(ModuleInstallRequest $request): bool
    {
        if (!$request->idempotencyKey) {
            return false;
        }
        
        return TenantModuleSubscription::where('idempotency_key', $request->idempotencyKey)
            ->where('tenant_id', $request->tenant->id)
            ->where('created_at', '>=', now()->subHour())
            ->exists();
    }

    private function findExistingSubscription(ModuleInstallRequest $request): TenantModuleSubscription
    {
        return TenantModuleSubscription::where('idempotency_key', $request->getIdempotencyKey())
            ->where('tenant_id', $request->tenant->id)
            ->first();
    }

    private function getTrialDays(string $moduleKey, Tenant $tenant): int
    {
        $plan = $tenant->plan;
        if (!$plan) {
            return 0;
        }
        
        $planModule = $plan->planModules()
            ->where('module_key', $moduleKey)
            ->first();
        
        return $planModule?->getTrialDays() ?? 0;
    }

    private function calculateNextBilling(string $billingCycle): \Carbon\Carbon
    {
        return $billingCycle === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();
    }

    // Hook and migration methods would be implemented here...
    private function runPreInstallHooks($module, $tenant): void {}
    private function runMigrations($module, $tenant): void {}
    private function runSeeders($module, $tenant): void {}
    private function runPostInstallHooks($module, $tenant): void {}
    private function purgeModuleData($subscription): void {}
}
```

### 3.2 Pricing Calculator

```php
<?php
// app/Services/Modules/PricingCalculator.php

namespace App\Services\Modules;

use App\DTOs\ModulePrice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;

class PricingCalculator
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Calculate the price for a module for a specific tenant.
     */
    public function calculate(string $moduleKey, Tenant $tenant, string $billingCycle = 'monthly'): ModulePrice
    {
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module) {
            return new ModulePrice();
        }

        // Core modules are always free
        if ($module->isCore()) {
            return new ModulePrice(
                monthly: null,
                yearly: null,
                setupFee: 0,
                currency: 'KES',
            );
        }

        // Check plan for overrides
        $planModule = $this->getPlanModule($moduleKey, $tenant->plan);
        
        if ($planModule?->is_included) {
            return new ModulePrice(
                monthly: 0,
                yearly: 0,
                setupFee: 0,
                currency: 'KES',
            );
        }

        // Get base price from plan override or module default
        $monthly = $planModule?->price_monthly_override ?? $module->price_monthly;
        $yearly = $planModule?->price_yearly_override ?? $module->price_yearly;
        $setupFee = $planModule?->setup_fee_override ?? $module->setup_fee;

        // Calculate yearly savings
        $yearlySavings = null;
        if ($monthly && $yearly) {
            $monthlyCost = $monthly * 12;
            $yearlySavings = (int) round((($monthlyCost - $yearly) / $monthlyCost) * 100);
        }

        return new ModulePrice(
            monthly: $monthly,
            yearly: $yearly,
            setupFee: $setupFee,
            yearlySavingsPercent: $yearlySavings,
            currency: 'KES',
        );
    }

    /**
     * Calculate prorated price for mid-cycle upgrades.
     */
    public function calculateProrated(
        string $moduleKey, 
        Tenant $tenant, 
        string $billingCycle,
        ?\Carbon\Carbon $startDate = null
    ): ModulePrice {
        $basePrice = $this->calculate($moduleKey, $tenant, $billingCycle);
        $startDate = $startDate ?? now();
        
        // Get current billing period info
        $subscription = $tenant->activeSubscription();
        if (!$subscription) {
            return $basePrice;
        }

        $periodEnd = $subscription->period_ends_at ?? now()->addMonth();
        $daysRemaining = now()->diffInDays($periodEnd);
        $totalDays = $subscription->period_starts_at->diffInDays($periodEnd);
        
        if ($totalDays <= 0) {
            return $basePrice;
        }

        $prorationFactor = $daysRemaining / $totalDays;
        
        $price = $basePrice->getPrice($billingCycle);
        $proratedPrice = $price ? round($price * $prorationFactor, 2) : null;
        $proratedSetupFee = $basePrice->setupFee;

        return new ModulePrice(
            monthly: $billingCycle === 'monthly' ? $proratedPrice : null,
            yearly: $billingCycle === 'yearly' ? $proratedPrice : null,
            setupFee: $proratedSetupFee,
            currency: $basePrice->currency,
        );
    }

    /**
     * Calculate total monthly cost for all tenant add-ons.
     */
    public function calculateTotalAddonCost(Tenant $tenant): array
    {
        $subscriptions = $tenant->modules()
            ->whereHas('subscription', function ($q) {
                $q->whereIn('billing_type', ['addon_monthly', 'addon_yearly']);
            })
            ->with('subscription')
            ->get();

        $monthly = 0;
        $yearly = 0;
        $breakdown = [];

        foreach ($subscriptions as $tm) {
            $sub = $tm->subscription;
            if (!$sub) continue;

            $price = $this->calculate($tm->module, $tenant, 'monthly');
            
            if ($sub->billing_type === 'addon_yearly') {
                $yearly += $sub->price ?? 0;
                // Convert to monthly equivalent for display
                $monthlyEquivalent = ($sub->price ?? 0) / 12;
            } else {
                $monthly += $sub->price ?? 0;
                $monthlyEquivalent = $sub->price ?? 0;
            }

            $breakdown[] = [
                'module' => $tm->module,
                'name' => $this->moduleRepository->findByKey($tm->module)?->name ?? $tm->module,
                'monthly_equivalent' => round($monthlyEquivalent, 2),
                'billing_type' => $sub->billing_type,
                'actual_price' => $sub->price,
            ];
        }

        return [
            'monthly' => round($monthly, 2),
            'yearly' => round($yearly, 2),
            'total_monthly_equivalent' => round($monthly + ($yearly / 12), 2),
            'breakdown' => $breakdown,
        ];
    }

    private function getPlanModule(string $moduleKey, ?Plan $plan): ?\App\Models\PlanModule
    {
        if (!$plan) {
            return null;
        }

        return $plan->planModules()
            ->where('module_key', $moduleKey)
            ->first();
    }
}
```

---

## Phase 4: SuperAdmin Interface

### 4.1 Controllers

```php
<?php
// app/Http/Controllers/SuperAdmin/ModuleController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->middleware('auth:superadmin');
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Display module catalog.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'price_type', 'search', 'status']);
        $modules = $this->moduleRepository->search($filters, 20);
        $stats = $this->getModuleStats();

        return view('superadmin.modules.index', compact('modules', 'stats'));
    }

    /**
     * Show module creation form.
     */
    public function create()
    {
        $categories = \App\Models\ModuleCategory::active()->get();
        $allModules = Module::active()->pluck('name', 'key');

        return view('superadmin.modules.create', compact('categories', 'allModules'));
    }

    /**
     * Store new module.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        
        $validated['created_by'] = auth('superadmin')->id();
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['key']);

        $module = Module::create($validated);

        return redirect()
            ->route('superadmin.modules.edit', $module)
            ->with('success', "Module '{$module->name}' created successfully.");
    }

    /**
     * Show module edit form.
     */
    public function edit(Module $module)
    {
        $categories = \App\Models\ModuleCategory::active()->get();
        $allModules = Module::active()->where('key', '!=', $module->key)->pluck('name', 'key');
        $changelogs = $module->changelogs()->orderBy('released_at', 'desc')->get();
        $tenantStats = $this->getTenantStats($module);

        return view('superadmin.modules.edit', compact(
            'module', 'categories', 'allModules', 'changelogs', 'tenantStats'
        ));
    }

    /**
     * Update module.
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate($this->validationRules());
        
        $validated['updated_by'] = auth('superadmin')->id();

        $module->update($validated);

        // Invalidate cache
        app(\App\Repositories\CachedModuleRepository::class)->invalidateModule($module->key);

        return redirect()
            ->back()
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Display plan-module matrix.
     */
    public function planMatrix()
    {
        $plans = \App\Models\Plan::with(['planModules.module'])->get();
        $modules = Module::orderBy('category')->orderBy('sort_order')->get();

        return view('superadmin.modules.plan-matrix', compact('plans', 'modules'));
    }

    /**
     * Update plan-module assignments.
     */
    public function updatePlanMatrix(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.plan_id' => 'required|exists:plans,id',
            'assignments.*.module_key' => 'required|exists:modules,key',
            'assignments.*.is_included' => 'boolean',
            'assignments.*.is_available' => 'boolean',
        ]);

        foreach ($validated['assignments'] as $assignment) {
            \App\Models\PlanModule::updateOrCreate(
                [
                    'plan_id' => $assignment['plan_id'],
                    'module_key' => $assignment['module_key'],
                ],
                [
                    'is_included' => $assignment['is_included'] ?? false,
                    'is_available' => $assignment['is_available'] ?? false,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Display module usage analytics.
     */
    public function analytics(Module $module)
    {
        $adoptionStats = $this->getAdoptionStats($module);
        $revenueStats = $this->getRevenueStats($module);
        $usageTrends = $this->getUsageTrends($module);

        return view('superadmin.modules.analytics', compact(
            'module', 'adoptionStats', 'revenueStats', 'usageTrends'
        ));
    }

    private function validationRules(): array
    {
        return [
            'key' => 'required|string|max:50|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:255',
            'category' => 'required|string|max:50',
            'tags' => 'nullable|array',
            'version' => 'nullable|string|max:20',
            'dependencies' => 'nullable|array',
            'conflicts' => 'nullable|array',
            'is_free' => 'boolean',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'default_limits' => 'nullable|array',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    private function getModuleStats(): array
    {
        return [
            'total' => Module::count(),
            'active' => Module::active()->count(),
            'free' => Module::free()->count(),
            'paid' => Module::paid()->count(),
            'total_installs' => \App\Models\TenantModuleSubscription::installed()->count(),
        ];
    }

    private function getTenantStats(Module $module): array
    {
        return [
            'total_installs' => $module->tenantSubscriptions()->installed()->count(),
            'active_tenants' => $module->tenantSubscriptions()->active()->count(),
            'trial_installs' => $module->tenantSubscriptions()->inTrial()->count(),
            'recent_installs' => $module->tenantSubscriptions()
                ->where('installed_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    // Analytics methods would be implemented here...
    private function getAdoptionStats($module): array { return []; }
    private function getRevenueStats($module): array { return []; }
    private function getUsageTrends($module): array { return []; }
}
```

---

## Phase 5: Tenant Marketplace

### 5.1 Controllers

```php
<?php
// app/Http/Controllers/Dashboard/MarketplaceController.php

namespace App\Http\Controllers\Dashboard;

use App\DTOs\ModuleInstallRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Services\Modules\ModuleInstaller;
use App\Services\Modules\PricingCalculator;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    private ModuleRepositoryInterface $moduleRepository;
    private ModuleInstaller $installer;
    private PricingCalculator $pricingCalculator;

    public function __construct(
        ModuleRepositoryInterface $moduleRepository,
        ModuleInstaller $installer,
        PricingCalculator $pricingCalculator
    ) {
        $this->middleware(['auth', 'tenant.active']);
        $this->moduleRepository = $moduleRepository;
        $this->installer = $installer;
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Browse marketplace modules.
     */
    public function index(Request $request)
    {
        $tenant = tenant();
        
        $filters = $request->only(['category', 'price_type', 'search']);
        $filters['sort_by'] = $request->get('sort_by', 'sort_order');
        
        $modules = $this->moduleRepository->search($filters, 12);
        $categories = \App\Models\ModuleCategory::active()->get();
        
        // Enrich with tenant-specific info
        foreach ($modules as $module) {
            $module->can_install = $this->moduleRepository->canInstall($module->key, $tenant);
            $module->is_installed = $tenant->hasModule($module->key);
            $module->price_info = $this->pricingCalculator->calculate($module->key, $tenant);
            $module->dependencies = $this->moduleRepository->getDependencyStatus($module->key, $tenant);
        }

        return view('dashboard.marketplace.index', compact('modules', 'categories'));
    }

    /**
     * Show module details.
     */
    public function show(string $moduleKey)
    {
        $tenant = tenant();
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module || !$module->is_public) {
            abort(404);
        }

        $module->can_install = $this->moduleRepository->canInstall($moduleKey, $tenant);
        $module->is_installed = $tenant->hasModule($moduleKey);
        $module->price_info = $this->pricingCalculator->calculate($moduleKey, $tenant);
        $module->dependencies = $this->moduleRepository->getDependencyStatus($moduleKey, $tenant);
        
        // Check if available on plan
        $planModule = $tenant->plan?->planModules()
            ->where('module_key', $moduleKey)
            ->first();

        return view('dashboard.marketplace.show', compact('module', 'planModule'));
    }

    /**
     * Show installation confirmation.
     */
    public function installForm(string $moduleKey)
    {
        $tenant = tenant();
        $module = $this->moduleRepository->findByKey($moduleKey);
        
        if (!$module) {
            abort(404);
        }

        if (!$this->moduleRepository->canInstall($moduleKey, $tenant)) {
            return redirect()
                ->route('marketplace.index')
                ->with('error', 'This module cannot be installed on your current plan.');
        }

        $dependencies = $this->moduleRepository->getDependencyStatus($moduleKey, $tenant);
        $price = $this->pricingCalculator->calculate($moduleKey, $tenant);
        
        // Get prorated price if applicable
        $prorated = $this->pricingCalculator->calculateProrated($moduleKey, $tenant, 'monthly');

        return view('dashboard.marketplace.install', compact(
            'module', 'dependencies', 'price', 'prorated'
        ));
    }

    /**
     * Process module installation.
     */
    public function install(Request $request, string $moduleKey)
    {
        $tenant = tenant();
        
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'agree_terms' => 'required|accepted',
        ]);

        if (!$this->moduleRepository->canInstall($moduleKey, $tenant)) {
            return response()->json([
                'success' => false,
                'error' => 'Module cannot be installed',
            ], 403);
        }

        $installRequest = new ModuleInstallRequest(
            moduleKey: $moduleKey,
            tenant: $tenant,
            requestedBy: $request->user(),
            billingCycle: $validated['billing_cycle'],
        );

        try {
            // Queue installation for background processing
            $subscription = $this->installer->queueInstall($installRequest);

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'redirect_url' => route('marketplace.installation-status', $subscription),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check installation status.
     */
    public function installationStatus(\App\Models\TenantModuleSubscription $subscription)
    {
        $this->authorize('view', $subscription);

        return response()->json([
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'progress' => $subscription->getInstallationProgress(),
            'log' => $subscription->installation_log,
            'error' => $subscription->installation_error,
        ]);
    }

    /**
     * List my installed modules.
     */
    public function myModules()
    {
        $tenant = tenant();
        
        $subscriptions = $tenant->modules()
            ->with('subscription')
            ->get()
            ->map(function ($tm) {
                $module = $this->moduleRepository->findByKey($tm->module);
                $tm->module_info = $module;
                $tm->price_info = $this->pricingCalculator->calculate($tm->module, $tm->tenant);
                return $tm;
            });

        $addonCost = $this->pricingCalculator->calculateTotalAddonCost($tenant);

        return view('dashboard.marketplace.my-modules', compact('subscriptions', 'addonCost'));
    }

    /**
     * Uninstall a module.
     */
    public function uninstall(Request $request, \App\Models\TenantModuleSubscription $subscription)
    {
        $this->authorize('delete', $subscription);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
            'purge_data' => 'boolean',
        ]);

        try {
            $this->installer->uninstall(
                $subscription, 
                $validated['reason'] ?? null,
                $validated['purge_data'] ?? false
            );

            return redirect()
                ->route('marketplace.my-modules')
                ->with('success', 'Module uninstalled successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
```

---

## Phase 6: Billing Integration

### 6.1 Billing Service

```php
<?php
// app/Services/Modules/ModuleBillingService.php

namespace App\Services\Modules;

use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use Illuminate\Support\Facades\Log;

class ModuleBillingService
{
    private PricingCalculator $pricingCalculator;

    public function __construct(PricingCalculator $pricingCalculator)
    {
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Process billing for a module subscription.
     */
    public function processBilling(TenantModuleSubscription $subscription): bool
    {
        if (!$subscription->isRecurring()) {
            return true;
        }

        // Check if due for billing
        if ($subscription->next_billing_at && $subscription->next_billing_at->isFuture()) {
            return true;
        }

        $tenant = $subscription->tenant;
        
        try {
            // Create invoice item
            $invoiceItem = $this->createInvoiceItem($subscription);
            
            // Process payment
            $paymentResult = $this->processPayment($subscription, $invoiceItem);
            
            if ($paymentResult['success']) {
                $subscription->update([
                    'last_billed_at' => now(),
                    'next_billing_at' => $this->calculateNextBilling($subscription),
                ]);
                
                event(new \App\Events\ModuleBilled($subscription, $invoiceItem));
                
                return true;
            } else {
                $this->handleBillingFailure($subscription, $paymentResult['error']);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Module billing failed', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->handleBillingFailure($subscription, $e->getMessage());
            return false;
        }
    }

    /**
     * Process billing for all due subscriptions.
     */
    public function processDueBillings(): array
    {
        $due = TenantModuleSubscription::pendingBilling()->get();
        
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
        ];

        foreach ($due as $subscription) {
            $results['processed']++;
            
            if ($this->processBilling($subscription)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Handle trial conversion to paid.
     */
    public function convertTrial(TenantModuleSubscription $subscription): bool
    {
        if (!$subscription->trialExpired()) {
            return false;
        }

        $price = $this->pricingCalculator->calculate(
            $subscription->module_key,
            $subscription->tenant,
            'monthly'
        );

        $subscription->update([
            'billing_type' => 'addon_monthly',
            'price' => $price->monthly,
            'next_billing_at' => now()->addMonth(),
        ]);

        // Process first payment
        return $this->processBilling($subscription);
    }

    /**
     * Prorate and refund on cancellation.
     */
    public function calculateCancellationRefund(TenantModuleSubscription $subscription): ?float
    {
        if (!$subscription->isRecurring()) {
            return null;
        }

        if (!$subscription->last_billed_at) {
            return null;
        }

        $periodStart = $subscription->last_billed_at;
        $periodEnd = $subscription->next_billing_at;
        $daysUsed = $periodStart->diffInDays(now());
        $totalDays = $periodStart->diffInDays($periodEnd);

        if ($totalDays <= 0) {
            return null;
        }

        $unusedDays = max(0, $totalDays - $daysUsed);
        $dailyRate = $subscription->price / $totalDays;

        return round($unusedDays * $dailyRate, 2);
    }

    private function createInvoiceItem(TenantModuleSubscription $subscription): array
    {
        return [
            'description' => "Module: " . $subscription->module?->name ?? $subscription->module_key,
            'amount' => $subscription->price,
            'currency' => $subscription->currency,
            'period_start' => $subscription->last_billed_at ?? $subscription->installed_at,
            'period_end' => $subscription->next_billing_at,
        ];
    }

    private function processPayment($subscription, $invoiceItem): array
    {
        // Integrate with existing payment gateway
        // This is a placeholder - use actual payment service
        return ['success' => true, 'transaction_id' => 'mock_' . uniqid()];
    }

    private function handleBillingFailure($subscription, $error): void
    {
        $subscription->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => 'billing_failed: ' . $error,
        ]);

        // Disable module
        \App\Models\TenantModule::where('tenant_id', $subscription->tenant_id)
            ->where('module', $subscription->module_key)
            ->update(['is_enabled' => false]);

        event(new \App\Events\ModuleBillingFailed($subscription, $error));
    }

    private function calculateNextBilling($subscription): \Carbon\Carbon
    {
        return $subscription->billing_type === 'addon_yearly'
            ? now()->addYear()
            : now()->addMonth();
    }
}
```

---

## Phase 7: Performance & Observability

### 7.1 Queue Configuration

```php
<?php
// config/queue-marketplace.php

return [
    // Dedicated queue for module installations
    'installations' => [
        'connection' => 'redis',
        'queue' => 'module-installations',
        'retry_after' => 3600, // 1 hour
        'timeout' => 300, // 5 minutes
    ],
    
    // Queue for billing operations
    'billing' => [
        'connection' => 'redis',
        'queue' => 'module-billing',
        'retry_after' => 1800,
    ],
    
    // Queue for analytics and reporting
    'analytics' => [
        'connection' => 'redis',
        'queue' => 'module-analytics',
        'retry_after' => 3600,
    ],
];
```

### 7.2 Monitoring & Alerts

```php
<?php
// app/Console/Commands/ModuleHealthCheck.php

namespace App\Console\Commands;

use App\Models\TenantModuleSubscription;
use Illuminate\Console\Command;

class ModuleHealthCheck extends Command
{
    protected $signature = 'modules:health-check';
    protected $description = 'Check module system health';

    public function handle(): int
    {
        $issues = [];

        // Check for stuck installations
        $stuck = TenantModuleSubscription::where('status', 'installing')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->count();
        
        if ($stuck > 0) {
            $issues[] = "{$stuck} installations stuck for >30 minutes";
        }

        // Check for failed installations
        $failed = TenantModuleSubscription::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        if ($failed > 10) {
            $issues[] = "{$failed} failed installations in last 24h";
        }

        // Check for billing failures
        $billingFailed = TenantModuleSubscription::where('status', 'suspended')
            ->where('suspension_reason', 'like', 'billing_failed%')
            ->where('suspended_at', '>=', now()->subDay())
            ->count();
        
        if ($billingFailed > 5) {
            $issues[] = "{$billingFailed} billing failures in last 24h";
        }

        if (!empty($issues)) {
            // Send alert
            \Log::warning('Module Health Check: Issues detected', $issues);
            // Notify admin...
            return 1;
        }

        $this->info('Module system healthy');
        return 0;
    }
}
```

---

## Appendices

### A. Event Listeners

```php
<?php
// app/Listeners/ModuleEventSubscriber.php

namespace App\Listeners;

use Illuminate\Events\Dispatcher;

class ModuleEventSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            \App\Events\ModuleInstalled::class,
            [self::class, 'handleModuleInstalled']
        );
        
        $events->listen(
            \App\Events\ModuleUninstalled::class,
            [self::class, 'handleModuleUninstalled']
        );
        
        $events->listen(
            \App\Events\ModuleBillingFailed::class,
            [self::class, 'handleBillingFailed']
        );
    }

    public function handleModuleInstalled($event): void
    {
        // Clear caches
        app(\App\Repositories\CachedModuleRepository::class)
            ->invalidateTenant($event->tenant->id);
        
        // Log to audit
        \App\Models\PlatformAuditLog::record(
            'module.installed',
            [
                'module_key' => $event->module->key,
                'tenant_id' => $event->tenant->id,
                'billing_type' => $event->subscription->billing_type,
            ],
            $event->tenant->id,
            $event->installedBy?->id
        );
        
        // Send notification
        $event->tenant->owner?->notify(new \App\Notifications\ModuleInstalled($event->module));
    }

    public function handleModuleUninstalled($event): void
    {
        app(\App\Repositories\CachedModuleRepository::class)
            ->invalidateTenant($event->tenant->id);
    }

    public function handleBillingFailed($event): void
    {
        // Notify tenant admin
        $event->subscription->tenant->owner?->notify(
            new \App\Notifications\ModuleBillingFailed($event->subscription)
        );
    }
}
```

### B. Testing Strategy

```php
<?php
// tests/Feature/Modules/MarketplaceTest.php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed test data
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\ModuleRegistrySeeder::class);
    }

    /** @test */
    public function tenant_can_browse_marketplace()
    {
        $tenant = Tenant::factory()->create(['plan_id' => Plan::where('slug', 'starter')->first()->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)
            ->get(route('marketplace.index'));

        $response->assertOk()
            ->assertViewHas('modules');
    }

    /** @test */
    public function tenant_can_install_available_module()
    {
        $tenant = Tenant::factory()->create(['plan_id' => Plan::where('slug', 'pro')->first()->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $module = Module::where('key', 'discipleship')->first();

        $response = $this->actingAs($user)
            ->postJson(route('marketplace.install', $module->key), [
                'billing_cycle' => 'monthly',
                'agree_terms' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tenant_module_subscriptions', [
            'tenant_id' => $tenant->id,
            'module_key' => $module->key,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function tenant_cannot_install_unavailable_module()
    {
        $tenant = Tenant::factory()->create(['plan_id' => Plan::where('slug', 'free')->first()->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        
        $module = Module::where('key', 'api_access')->first();

        $response = $this->actingAs($user)
            ->postJson(route('marketplace.install', $module->key), [
                'billing_cycle' => 'monthly',
                'agree_terms' => true,
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function installation_respects_dependencies()
    {
        // Test that installing a module with unmet dependencies fails
    }

    /** @test */
    public function installation_is_idempotent()
    {
        // Test that duplicate installation requests return same result
    }

    /** @test */
    public function billing_is_calculated_correctly()
    {
        // Test pricing calculations including proration
    }
}
```

### C. API Endpoints Reference

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/marketplace/modules` | List available modules | Tenant |
| GET | `/api/marketplace/modules/{key}` | Module details | Tenant |
| POST | `/api/marketplace/modules/{key}/install` | Install module | Tenant |
| GET | `/api/marketplace/installations/{id}/status` | Check installation status | Tenant |
| GET | `/api/marketplace/my-modules` | List installed modules | Tenant |
| DELETE | `/api/marketplace/my-modules/{id}` | Uninstall module | Tenant |
| GET | `/api/superadmin/modules` | List all modules | SuperAdmin |
| POST | `/api/superadmin/modules` | Create module | SuperAdmin |
| PUT | `/api/superadmin/modules/{key}` | Update module | SuperAdmin |
| GET | `/api/superadmin/plan-matrix` | Plan-module matrix | SuperAdmin |
| PUT | `/api/superadmin/plan-matrix` | Update matrix | SuperAdmin |
| GET | `/api/superadmin/modules/{key}/analytics` | Module analytics | SuperAdmin |

---

**Document End**
