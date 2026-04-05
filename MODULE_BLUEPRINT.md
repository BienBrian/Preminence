# Module Blueprint - Complete Guide for Building Modules

## Table of Contents
1. [Overview](#overview)
2. [Module Architecture](#module-architecture)
3. [Directory Structure](#directory-structure)
4. [Step-by-Step Implementation](#step-by-step-implementation)
5. [Core Integration Points](#core-integration-points)
6. [Permission System](#permission-system)
7. [Database Schema](#database-schema)
8. [UI/UX Guidelines](#uiux-guidelines)
9. [Testing Checklist](#testing-checklist)
10. [Example: Complete Module](#example-complete-module)

---

## Overview

This blueprint defines the standard structure for building modules in the Pisti Church Management System. Following this guide ensures:
- Consistent integration with the core system
- Proper permission-based access control
- Module activation/deactivation support
- Navigation integration
- Standardized onboarding flow

---

## Module Architecture

### Core Principles
1. **Modularity**: Each module is self-contained
2. **Optional**: Users can enable/disable modules
3. **Permission-based**: Fine-grained access control
4. **Tenant-scoped**: All data is isolated by tenant
5. **Dependency-aware**: Modules declare dependencies

### Module Lifecycle
```
Registered → Installed → Activated → Onboarded → Active
                ↓              ↓              ↓
            Config        Permissions    Tutorial
            Created       Assigned       Shown
```

---

## Directory Structure

```
pisti-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── [ModuleName]/           # Module controllers
│   │           └── [Module]Controller.php
│   ├── Models/
│   │   └── [ModuleModel].php           # Module-specific models
│   ├── Services/
│   │   └── [Module]Service.php         # Business logic
│   ├── Mail/
│   │   └── [Module]Mail.php            # Email classes
│   └── ...
├── database/
│   ├── migrations/
│   │   └── YYYY_MM_DD_HHMMSS_create_[module]_tables.php
│   └── seeders/
│       └── [Module]ModuleSeeder.php    # Module registration
├── resources/
│   └── views/
│       └── [module_name]/              # Blade templates
│           ├── index.blade.php
│           ├── create.blade.php
│           └── ...
└── routes/
    └── web.php                          # Module routes
```

---

## Step-by-Step Implementation

### Step 1: Register Module

Create a seeder in `database/seeders/[Module]ModuleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleOnboardingConfig;
use App\Models\ModuleActivationSettings;
use Illuminate\Database\Seeder;

class ExampleModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedModule();
        $this->seedPermissions();  // If using custom permissions
        $this->seedOnboardingConfig();
        $this->seedActivationSettings();
        $this->activateForExistingTenants();
    }

    private function seedModule(): void
    {
        Module::firstOrCreate(
            ['key' => 'example_module'],
            [
                'name' => 'Example Module',
                'slug' => 'example-module',
                'description' => 'Full description of what this module does',
                'short_description' => 'Brief description for cards',
                'category' => 'reports',  // or 'financial', 'communication', etc.
                'tags' => ['reports', 'analytics', 'members'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-graph-up',  // Bootstrap icon class
                'is_active' => true,
                'is_public' => true,
                'features' => ['feature_1', 'feature_2'],
                'dependencies' => ['finance', 'people'],  // Required modules
            ]
        );
    }

    private function seedPermissions(): void
    {
        $seeder = new ExampleModulePermissionSeeder();
        $seeder->setCommand($this->command);
        $seeder->run();
    }

    private function seedOnboardingConfig(): void
    {
        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'example_module'],
            [
                'onboarding_type' => 'guided',  // or 'instant', 'setup_wizard', 'kyc'
                'requires_approval' => false,
                'welcome_message' => 'Welcome to Example Module!',
                'completion_message' => 'Setup complete!',
                'estimated_setup_time_minutes' => 5,
                'tutorial_content' => [
                    'steps' => [
                        [
                            'title' => 'Step 1 Title',
                            'content' => 'Step description',
                            'icon' => 'bi-info-circle',
                        ],
                    ],
                ],
                'contextual_help_enabled' => true,
                'contextual_help_content' => [
                    'tooltips' => [
                        [
                            'target' => '#element-id',
                            'title' => 'Tooltip Title',
                            'content' => 'Help text',
                            'position' => 'top',
                        ],
                    ],
                ],
                'is_configured' => true,
                'configured_at' => now(),
            ]
        );
    }

    private function seedActivationSettings(): void
    {
        ModuleActivationSettings::firstOrCreate(
            ['module_key' => 'example_module'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
                'minimum_plan_tier' => null,
                'activation_messages' => [
                    'activated' => 'Module activated successfully!',
                ],
            ]
        );
    }

    private function activateForExistingTenants(): void
    {
        // Optional: Auto-activate for tenants with dependencies
        $tenantsWithDependencies = Tenant::whereHas('modules', function ($query) {
            $query->where('module', 'finance')->where('is_enabled', true);
        })->get();

        foreach ($tenantsWithDependencies as $tenant) {
            // Create TenantModule and TenantModuleSubscription records
            // ... implementation
        }
    }
}
```

### Step 2: Create Permissions

Create `database/seeders/[Module]PermissionSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ExampleModulePermissionSeeder extends Seeder
{
    private const MODULE_KEY = 'example_module';

    private const PERMISSIONS = [
        [
            'permission_key' => 'view',
            'name' => 'View Example Module',
            'description' => 'Access the module dashboard',
            'level' => 'basic',
            'is_premium' => false,
        ],
        [
            'permission_key' => 'create',
            'name' => 'Create Examples',
            'description' => 'Create new records',
            'level' => 'basic',
            'is_premium' => false,
        ],
        [
            'permission_key' => 'edit',
            'name' => 'Edit Examples',
            'description' => 'Edit existing records',
            'level' => 'basic',
            'is_premium' => false,
        ],
        [
            'permission_key' => 'delete',
            'name' => 'Delete Examples',
            'description' => 'Delete records',
            'level' => 'premium',
            'is_premium' => true,
        ],
        [
            'permission_key' => 'configure',
            'name' => 'Configure Example Module',
            'description' => 'Manage module settings',
            'level' => 'advanced',
            'is_premium' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $index => $permissionData) {
            $this->createPermission($permissionData, $index);
        }
    }

    private function createPermission(array $data, int $sortOrder): void
    {
        $name = $data['name'];

        // 1. Create Spatie Permission
        $spatiePermission = Permission::firstOrCreate(
            ['name' => $name],
            ['guard_name' => 'web']
        );

        // 2. Create Module Permission
        ModulePermission::firstOrCreate(
            [
                'module_key' => self::MODULE_KEY,
                'permission_key' => $data['permission_key'],
            ],
            [
                'name' => $name,
                'description' => $data['description'],
                'level' => $data['level'],
                'is_premium' => $data['is_premium'],
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]
        );

        // 3. Grant to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($spatiePermission);
        }
    }
}
```

### Step 3: Create Database Tables

Migration naming: `YYYY_MM_DD_HHMMSS_create_[module]_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('example_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('example_records');
    }
};
```

### Step 4: Create Models

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExampleRecord extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'description',
        'amount',
        'metadata',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
```

### Step 5: Create Controller

```php
<?php

namespace App\Http\Controllers\[ModuleName];

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\ExampleRecord;
use App\Services\ExampleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExampleController extends DashboardController
{
    private ExampleService $service;

    public function __construct(ExampleService $service)
    {
        parent::__construct();
        $this->service = $service;

        // Permission middleware
        $this->middleware(['permission:View Example Module'], ['only' => ['index']]);
        $this->middleware(['permission:Create Examples'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:Edit Examples'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:Delete Examples'], ['only' => ['destroy']]);
        $this->middleware(['permission:Configure Example Module'], ['only' => ['settings']]);
    }

    public function index()
    {
        $tenant = auth()->user()->tenant;
        $records = ExampleRecord::forTenant($tenant->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('example_module.index', compact('records'));
    }

    // ... other methods
}
```

### Step 6: Define Routes

In `routes/web.php`:

```php
// [Module Name] Module Routes
Route::group(['middleware' => 'module:example_module'], function () {
    Route::get('example-module', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'index']);
    Route::get('example-module/create', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'create']);
    Route::post('example-module', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'store']);
    Route::get('example-module/{id}/edit', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'edit']);
    Route::put('example-module/{id}', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'update']);
    Route::delete('example-module/{id}', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'destroy']);
    Route::get('example-module/settings', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'settings']);
    Route::post('example-module/settings', [\App\Http\Controllers\ExampleModule\ExampleController::class, 'updateSettings']);
});
```

### Step 7: Add Navigation

In `resources/views/layouts/dashboard.blade.php`, add to the module menus section:

```blade
@if(module('example_module'))
@can('View Example Module')
<div data-module="example" data-title="Example" data-icon="fas fa-chart-line">
    <ul class="ss-nav">
        <li><a href="{{ url('dashboard/example-module') }}" class="{{ Request::is('dashboard/example-module') ? 'active' : '' }}"><i class="fas fa-list"></i> <span>Dashboard</span></a></li>
        @can('Create Examples')
        <li><a href="{{ url('dashboard/example-module/create') }}"><i class="fas fa-plus"></i> <span>Create New</span></a></li>
        @endcan
        @can('Configure Example Module')
        <li><a href="{{ url('dashboard/example-module/settings') }}"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
        @endcan
    </ul>
</div>
@endcan
@endif
```

### Step 8: Add to ModuleService

In `app/Services/ModuleService.php`, add the module key to the MODULES constant:

```php
public const MODULES = [
    // ... existing modules
    'example_module',
];
```

---

## Core Integration Points

### 1. Tenant Context
All queries must be scoped to the current tenant:
```php
// Automatic via BelongsToTenant trait
$records = ExampleRecord::all(); // Already filtered by tenant

// Manual scoping
$records = ExampleRecord::forTenant($tenantId)->get();
```

### 2. User Authentication
```php
$user = auth()->user();
$tenant = $user->tenant;

// Check permissions
if ($user->can('Edit Examples')) {
    // Allow action
}
```

### 3. Module Middleware
The `module:module_key` middleware checks:
1. Module is registered
2. Module is active for current tenant
3. Tenant has required dependencies

### 4. Cache Management
Always flush module cache when making changes:
```php
app(ModuleService::class)->flushCache($tenantId, 'example_module');
```

---

## Permission System

### Permission Levels
- **basic**: Core functionality (view, create)
- **advanced**: Management features (edit, configure)
- **premium**: Administrative actions (delete, bulk operations)

### Checking Permissions

**In Controllers:**
```php
$this->middleware(['permission:View Examples']);
```

**In Views:**
```blade
@can('Edit Examples')
    <button>Edit</button>
@endcan
```

**In Code:**
```php
if (auth()->user()->can('Delete Examples')) {
    // Delete action
}
```

### Permission Inheritance
- Super Admin: All permissions automatically
- Custom Roles: Permissions assigned via Roles page
- Module Inactive: All permissions hidden from Roles page

---

## Database Schema

### Required Columns for Tenant Isolation
```php
$table->foreignId('tenant_id')->constrained()->onDelete('cascade');
```

### Standard Columns
```php
$table->id();
$table->foreignId('tenant_id')->constrained()->onDelete('cascade');
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
$table->string('title');
$table->text('description')->nullable();
$table->enum('status', ['active', 'inactive'])->default('active');
$table->timestamps();
$table->softDeletes(); // Optional

// Indexes
$table->index(['tenant_id', 'status']);
$table->index(['tenant_id', 'created_at']);
```

---

## UI/UX Guidelines

### 1. Layout Structure
```blade
@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <!-- Breadcrumbs and title -->
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <!-- Main content -->
        </div>
    </section>
@endsection
```

### 2. Permission-Based UI
```blade
@can('Create Examples')
    <a href="{{ url('dashboard/example-module/create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New
    </a>
@endcan
```

### 3. Toast Notifications
```javascript
// Success
toastr.success('Record created successfully');

// Error
toastr.error('Failed to create record');
```

---

## Testing Checklist

### Unit Tests
- [ ] Model relationships work
- [ ] Scopes filter correctly
- [ ] Service methods work

### Integration Tests
- [ ] Routes accessible with module active
- [ ] Routes blocked with module inactive
- [ ] Permissions enforced correctly
- [ ] Tenant isolation works

### UI Tests
- [ ] Navigation appears when module active
- [ ] Navigation hidden when module inactive
- [ ] Permissions show/hide UI elements
- [ ] Onboarding flow works

### E2E Tests
- [ ] Full CRUD workflow
- [ ] Settings save correctly
- [ ] Bulk operations work
- [ ] Email notifications send

---

## Example: Complete Module

See the `giving_statements` module for a complete, production-ready example:

### Files Reference:
- **Seeder**: `database/seeders/GivingStatementModuleSeeder.php`
- **Permissions**: `database/seeders/GivingStatementsPermissionSeeder.php`
- **Controller**: `app/Http/Controllers/GivingStatements/GivingStatementController.php`
- **Service**: `app/Services/GivingReportService.php`
- **Views**: `resources/views/giving_statements/`
- **Routes**: `routes/web.php` (Giving Statements section)

---

## Quick Reference Card

### Creating a New Module (Checklist)

1. [ ] Create `[Module]ModuleSeeder.php`
2. [ ] Create `[Module]PermissionSeeder.php`
3. [ ] Create database migrations
4. [ ] Create models with `BelongsToTenant` trait
5. [ ] Create controller extending `DashboardController`
6. [ ] Add routes in `web.php` with `module:` middleware
7. [ ] Create Blade views
8. [ ] Add navigation in `dashboard.blade.php`
9. [ ] Add module key to `ModuleService::MODULES`
10. [ ] Create permission seeder and run all seeders
11. [ ] Test with module active and inactive
12. [ ] Test permissions in Roles page

### Common Commands

```bash
# Create migration
php artisan make:migration create_module_tables

# Run module seeder
php artisan db:seed --class=ModuleSeeder

# Clear caches
php artisan cache:clear
php artisan view:clear

# Check routes
php artisan route:list --path=module-name
```

---

**Last Updated**: 2026-03-28
**Version**: 1.0
**Author**: Development Team
