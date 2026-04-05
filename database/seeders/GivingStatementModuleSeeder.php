<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleOnboardingConfig;
use App\Models\ModuleActivationSettings;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\TenantModuleSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Giving Statement Module Seeder
 * 
 * Registers the giving_statements module with onboarding configuration.
 */
class GivingStatementModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedModule();
        $this->seedPermissions();
        $this->seedOnboardingConfig();
        $this->seedActivationSettings();
        $this->activateForExistingTenants();
    }

    /**
     * Create module permissions
     */
    private function seedPermissions(): void
    {
        $this->command->info('Seeding Giving Statements permissions...');
        
        $seeder = new GivingStatementsPermissionSeeder();
        $seeder->setCommand($this->command);
        $seeder->run();
    }

    private function seedModule(): void
    {
        Module::firstOrCreate(
            ['key' => 'giving_statements'],
            [
                'name' => 'Giving Statements',
                'slug' => 'giving-statements',
                'description' => 'Generate personalized giving reports for members with password protection. Print or email annual statements for tax purposes.',
                'short_description' => 'Personalized giving reports with password protection',
                'category' => 'reports',
                'tags' => ['reports', 'finance', 'giving', 'tax', 'members'],
                'is_free' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'icon' => 'bi-file-earmark-text',
                'is_active' => true,
                'is_public' => true,
                'features' => [
                    'individual_reports',
                    'bulk_generation',
                    'password_protection',
                    'email_delivery',
                    'pdf_export',
                    'category_filtering',
                    'tax_statements',
                ],
                'dependencies' => ['finance', 'people'],
            ]
        );
    }

    private function seedOnboardingConfig(): void
    {
        ModuleOnboardingConfig::firstOrCreate(
            ['module_key' => 'giving_statements'],
            [
                'onboarding_type' => 'guided',
                'requires_approval' => false,
                'welcome_message' => 'Welcome to Giving Statements! Generate personalized giving reports for your church members.',
                'completion_message' => 'Your giving statements module is ready. Start generating reports for your members.',
                'estimated_setup_time_minutes' => 3,
                'tutorial_content' => [
                    'steps' => [
                        [
                            'title' => 'What are Giving Statements?',
                            'content' => 'Giving statements are personalized reports showing each member\'s contributions over a period. They\'re essential for tax purposes and donor appreciation.',
                            'icon' => 'bi-info-circle',
                        ],
                        [
                            'title' => 'Password Protection',
                            'content' => 'Reports can be password-protected using phone numbers, ID numbers, or custom codes. This keeps sensitive financial information secure.',
                            'icon' => 'bi-shield-lock',
                        ],
                        [
                            'title' => 'Email or Print',
                            'content' => 'Send statements directly to members\' verified email addresses, or print them for distribution. Bulk generation saves time for large congregations.',
                            'icon' => 'bi-send',
                        ],
                    ],
                ],
                'contextual_help_enabled' => true,
                'contextual_help_content' => [
                    'tooltips' => [
                        [
                            'target' => '#member-selector',
                            'title' => 'Select Members',
                            'content' => 'Choose one member for detailed reports or multiple for bulk generation.',
                            'position' => 'top',
                        ],
                        [
                            'target' => '#category-filter',
                            'title' => 'Filter Categories',
                            'content' => 'Select which giving categories to include (Tithes, Offerings, etc.).',
                            'position' => 'right',
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
            ['module_key' => 'giving_statements'],
            [
                'tenant_can_self_activate' => true,
                'requires_superadmin_approval' => false,
                'allow_trial' => false,
                'minimum_plan_tier' => null,
                'activation_messages' => [
                    'activated' => 'Giving Statements module activated! You can now generate personalized giving reports for your members.',
                ],
            ]
        );
    }

    /**
     * Activate giving_statements module for existing tenants that have
     * the required dependencies (finance and people).
     */
    private function activateForExistingTenants(): void
    {
        $this->command->info('Activating Giving Statements module for eligible tenants...');
        
        // Find all tenants that have both finance and people modules enabled
        $tenantsWithDependencies = Tenant::whereHas('modules', function ($query) {
            $query->where('module', 'finance')
                  ->where('is_enabled', true);
        })->whereHas('modules', function ($query) {
            $query->where('module', 'people')
                  ->where('is_enabled', true);
        })->get();

        $activatedCount = 0;
        $skippedCount = 0;

        foreach ($tenantsWithDependencies as $tenant) {
            // Check if already activated (TenantModule)
            $existingTenantModule = TenantModule::where('tenant_id', $tenant->id)
                ->where('module', 'giving_statements')
                ->first();

            // Check if subscription exists
            $existingSubscription = TenantModuleSubscription::where('tenant_id', $tenant->id)
                ->where('module_key', 'giving_statements')
                ->first();

            if ($existingTenantModule && $existingTenantModule->is_enabled && $existingSubscription) {
                $skippedCount++;
                continue; // Fully activated
            }

            // Create or update subscription record (for superadmin visibility)
            if ($existingSubscription) {
                if ($existingSubscription->status !== 'active') {
                    $existingSubscription->update([
                        'status' => 'active',
                        'billing_type' => 'plan_included',
                        'price' => 0,
                        'installed_at' => now(),
                        'next_billing_at' => null,
                    ]);
                }
                $subscription = $existingSubscription;
            } else {
                $subscription = TenantModuleSubscription::create([
                    'tenant_id' => $tenant->id,
                    'module_key' => 'giving_statements',
                    'status' => 'active',
                    'billing_type' => 'plan_included',
                    'price' => 0,
                    'currency' => $tenant->subscription?->currency ?? 'USD',
                    'installed_at' => now(),
                    'next_billing_at' => null,
                    'settings' => ['auto_activated_by_seeder' => true],
                ]);
            }

            // Create or update TenantModule record
            if ($existingTenantModule) {
                $existingTenantModule->update([
                    'is_enabled' => true,
                    'subscription_id' => $subscription->id,
                    'enabled_at' => now(),
                    'disabled_at' => null,
                ]);
            } else {
                TenantModule::create([
                    'tenant_id' => $tenant->id,
                    'module' => 'giving_statements',
                    'subscription_id' => $subscription->id,
                    'is_enabled' => true,
                    'enabled_at' => now(),
                ]);
            }

            // Clear caches
            cache()->forget("tenant_{$tenant->id}_module_giving_statements");
            
            $activatedCount++;
            $this->command->info("  ✓ Activated for tenant: {$tenant->name} (ID: {$tenant->id})");
        }

        $this->command->info("Activation complete: {$activatedCount} activated, {$skippedCount} already enabled.");
        
        Log::info('Giving Statements module activated for existing tenants', [
            'activated' => $activatedCount,
            'skipped' => $skippedCount,
        ]);
    }
}
