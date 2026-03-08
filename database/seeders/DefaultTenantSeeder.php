<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Seed Tenant #1 — Happy Church Ruiru (the pioneering customer).
     *
     * This maps all existing data (tenant_id will default to 1 after Phase 3
     * migrations add the tenant_id column). Also seeds the first superadmin.
     *
     * Safe to re-run: uses updateOrCreate throughout.
     */
    public function run(): void
    {
        // Ensure plans exist first
        if (Plan::count() === 0) {
            $this->call(PlansSeeder::class);
        }

        $proPlan = Plan::where('slug', 'pro')->first();

        // ─── Tenant #1: Happy Church Ruiru ───────────────────────────────────
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'happychurch-ruiru'],
            [
                'name'                  => 'Happy Church Ruiru',
                'slug'                  => 'happychurch-ruiru',
                'domain'                => null,
                'logo'                  => null,
                'status'                => 'active',    // already paying / established
                'plan_id'               => $proPlan?->id,
                'trial_ends_at'         => null,
                'subscription_ends_at'  => now()->addYear(), // give them a year
                'owner_user_id'         => null,            // linked after Phase 3
                'setup_complete'        => true,            // existing tenant, skip wizard
                'grace_period_days'     => 7,
            ]
        );

        $this->command->info("✅ Tenant #1 created: {$tenant->name} (ID: {$tenant->id})");

        // ─── Enable all Pro modules for Tenant #1 ───────────────────────────
        $proModules = $proPlan?->modules ?? [];
        foreach ($proModules as $module => $enabled) {
            TenantModule::updateOrCreate(
                ['tenant_id' => $tenant->id, 'module' => $module],
                [
                    'is_enabled'        => $enabled,
                    'override_by_admin' => false,
                    'overridden_by'     => null,
                    'enabled_at'        => $enabled ? now() : null,
                    'disabled_at'       => $enabled ? null : now(),
                ]
            );
        }

        $this->command->info('✅ Modules enabled for Tenant #1 (Pro plan)');

        // ─── Default Superadmin Account ──────────────────────────────────────
        // IMPORTANT: Change password immediately after first login!
        $superAdmin = SuperAdmin::updateOrCreate(
            ['email' => 'admin@pisti.co.ke'],
            [
                'name'      => 'Pisti Platform Admin',
                'email'     => 'admin@pisti.co.ke',
                'password'  => Hash::make('Change-Me-Now-2024!'),
                'is_active' => true,
            ]
        );

        $this->command->warn('⚠️  Default superadmin created: admin@pisti.co.ke / Change-Me-Now-2024!');
        $this->command->warn('   CHANGE THIS PASSWORD IMMEDIATELY before deploying to production!');
    }
}
