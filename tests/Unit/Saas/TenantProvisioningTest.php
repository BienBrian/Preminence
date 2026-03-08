<?php

namespace Tests\Unit\Saas;

use App\Jobs\TenantProvisioningJob;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a plan
        Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => 29.99,
            'billing_period' => 'monthly',
            'is_active' => true,
            'modules' => ['people', 'attendance', 'finance', 'sms'],
        ]);
    }

    /** @test */
    public function it_creates_tenant_with_correct_data()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_phone' => '254712345678',
            'admin_password' => 'SecurePass123!',
            'plan_id' => 1,
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        $this->assertNotNull($tenant);
        $this->assertEquals('new-test-church', $tenant->slug);
        $this->assertEquals('new-test-church', $tenant->subdomain);
        $this->assertEquals('new-test-church.happychurchruiru.org', $tenant->subdomain_url);
        $this->assertEquals('trial', $tenant->status);
        $this->assertNotNull($tenant->trial_ends_at);
    }

    /** @test */
    public function it_creates_admin_user_for_tenant()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_phone' => '254712345678',
            'admin_password' => 'SecurePass123!',
            'plan_id' => 1,
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        $user = User::where('email', 'john@newchurch.org')->first();
        
        $this->assertNotNull($user);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertEquals('John Doe', $user->firstname);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
    }

    /** @test */
    public function it_creates_default_funds_for_tenant()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        $this->assertDatabaseHas('funds', [
            'tenant_id' => $tenant->id,
            'name' => 'Tithes',
        ]);
        
        $this->assertDatabaseHas('funds', [
            'tenant_id' => $tenant->id,
            'name' => 'Special Offering',
        ]);
    }

    /** @test */
    public function it_enables_modules_based_on_plan()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
            'plan_id' => 1,
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        // Check that plan modules are enabled
        $this->assertDatabaseHas('tenant_modules', [
            'tenant_id' => $tenant->id,
            'module' => 'people',
            'is_enabled' => true,
        ]);
        
        $this->assertDatabaseHas('tenant_modules', [
            'tenant_id' => $tenant->id,
            'module' => 'finance',
            'is_enabled' => true,
        ]);
    }

    /** @test */
    public function it_generates_unique_slug_for_duplicate_names()
    {
        // Create first church
        Tenant::create([
            'name' => 'Test Church',
            'slug' => 'test-church',
            'subdomain' => 'test-church',
        ]);

        // Try to create another with same name
        $data = [
            'church_name' => 'Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john2@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenants = Tenant::where('name', 'Test Church')->get();
        
        $this->assertCount(2, $tenants);
        
        $slugs = $tenants->pluck('slug')->toArray();
        $this->assertContains('test-church', $slugs);
        $this->assertContains('test-church-1', $slugs);
    }

    /** @test */
    public function it_assigns_super_admin_role_to_admin_user()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $user = User::where('email', 'john@newchurch.org')->first();
        
        $this->assertTrue($user->hasRole('Super Admin'));
    }

    /** @test */
    public function it_creates_storage_directory_for_tenant()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        $storagePath = storage_path("app/tenants/{$tenant->id}");
        
        $this->assertDirectoryExists($storagePath);
        
        // Cleanup
        @rmdir($storagePath);
    }

    /** @test */
    public function it_logs_platform_audit_log_entry()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data, 1); // With superadmin ID 1

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        $this->assertDatabaseHas('platform_audit_log', [
            'action' => 'tenant.created',
            'tenant_id' => $tenant->id,
            'super_admin_id' => 1,
        ]);
    }

    /** @test */
    public function it_sets_correct_trial_end_date()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        $this->assertNotNull($tenant->trial_ends_at);
        $this->assertTrue($tenant->trial_ends_at->isFuture());
        // Should be approximately 14 days from now
        $this->assertTrue($tenant->trial_ends_at->diffInDays(now()) >= 13);
    }

    /** @test */
    public function it_creates_default_settings_for_tenant()
    {
        $data = [
            'church_name' => 'New Test Church',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@newchurch.org',
            'admin_password' => 'SecurePass123!',
        ];

        TenantProvisioningJob::dispatchSync($data);

        $tenant = Tenant::where('name', 'New Test Church')->first();
        
        $this->assertDatabaseHas('settings', [
            'tenant_id' => $tenant->id,
            'name' => 'New Test Church',
            'email' => 'john@newchurch.org',
        ]);
    }
}
