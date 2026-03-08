<?php

namespace App\Jobs;

use App\Models\Funds;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Models\PlatformAuditLog;
use App\Services\ModuleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantProvisioningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public int $superAdminId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, int $superAdminId = 1)
    {
        $this->data = $data;
        $this->superAdminId = $superAdminId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Create Tenant record
        $tenant = Tenant::create([
            'name' => $this->data['church_name'],
            'slug' => $this->generateSlug($this->data['church_name']),
            'domain' => $this->data['custom_domain'] ?? null,
            'plan_id' => $this->data['plan_id'] ?? null,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'setup_complete' => false,
            'grace_period_days' => 7,
        ]);

        // Set tenant context
        config(['app.tenant_id' => $tenant->id]);

        // 2. Create default settings for tenant
        Setting::create([
            'tenant_id' => $tenant->id,
            'name' => $this->data['church_name'],
            'email' => $this->data['admin_email'],
            'phone_code' => '254',
        ]);

        // 3. Create admin user with Super Admin role
        $user = User::create([
            'tenant_id' => $tenant->id,
            'firstname' => $this->data['admin_name'],
            'surname' => '',
            'email' => $this->data['admin_email'],
            'phone' => $this->data['admin_phone'] ?? null,
            'password' => Hash::make($this->data['admin_password']),
            'status' => 1,
        ]);

        // 4. Set up Spatie permissions for this tenant
        $this->setupTenantPermissions($tenant->id, $user);

        // 5. Create default fund sources
        $this->createDefaultFunds($tenant->id);

        // 6. Enable default modules for selected plan
        $this->enableDefaultModules($tenant);

        // 7. Create tenant storage directory
        $storagePath = tenant_storage_path();
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // 8. Send welcome email
        $this->sendWelcomeEmail($tenant, $user);

        // 9. Log to platform audit log
        PlatformAuditLog::record(
            action: 'tenant.created',
            details: [
                'tenant_name' => $tenant->name,
                'admin_email' => $user->email,
                'plan_id' => $tenant->plan_id,
            ],
            tenantId: $tenant->id,
            superAdminId: $this->superAdminId
        );
    }

    /**
     * Generate a unique slug from church name.
     */
    private function generateSlug(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $base = trim($base, '-');
        
        // Check for uniqueness
        $slug = $base;
        $counter = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Set up default roles and permissions for the tenant.
     */
    private function setupTenantPermissions(int $tenantId, User $adminUser): void
    {
        // Set team ID for Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        // Create default roles
        $roles = [
            'Super Admin' => 'Full access to all features',
            'Admin' => 'Administrative access with some restrictions',
            'User' => 'Regular member access',
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['tenant_id' => $tenantId]
            );
        }

        // Assign Super Admin role to the admin user
        $adminUser->assignRole('Super Admin');
    }

    /**
     * Create default fund sources for the tenant.
     */
    private function createDefaultFunds(int $tenantId): void
    {
        $defaultFunds = [
            ['name' => 'Tithes', 'description' => 'Regular tithes and offerings'],
            ['name' => 'Special Offering', 'description' => 'Special contributions'],
            ['name' => 'Building Fund', 'description' => 'Building and construction contributions'],
            ['name' => 'Missions', 'description' => 'Missionary support contributions'],
        ];

        foreach ($defaultFunds as $fund) {
            Funds::create([
                'tenant_id' => $tenantId,
                'name' => $fund['name'],
                'description' => $fund['description'],
                'amount' => 0,
            ]);
        }
    }

    /**
     * Enable default modules based on plan.
     */
    private function enableDefaultModules(Tenant $tenant): void
    {
        $plan = $tenant->plan;
        
        if (!$plan) {
            // Enable free tier modules only
            $defaultModules = ['people', 'attendance', 'events', 'spiritual'];
        } else {
            // Get modules from plan configuration
            $defaultModules = $plan->modules ?? ['people', 'attendance', 'events', 'spiritual'];
        }

        foreach ($defaultModules as $module) {
            TenantModule::create([
                'tenant_id' => $tenant->id,
                'module' => $module,
                'is_enabled' => true,
                'override_by_admin' => false,
                'enabled_at' => now(),
            ]);
        }
    }

    /**
     * Send welcome email to the new admin.
     */
    private function sendWelcomeEmail(Tenant $tenant, User $user): void
    {
        $loginUrl = "https://{$tenant->slug}.happychurchruiru.org/login";
        
        // Note: You'd need to create a Mailable for this
        // Mail::to($user->email)->send(new TenantWelcomeMail($tenant, $user, $loginUrl));
    }
}
