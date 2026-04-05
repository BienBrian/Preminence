<?php

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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
                'is_free' => true,
                'price_monthly' => 0.00,
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
            // Generate slug from key if not provided
            $moduleData['slug'] = $moduleData['slug'] ?? $moduleData['key'];
            
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
                'reports' => ['is_included' => true],
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
                'reports' => ['is_included' => true],
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
