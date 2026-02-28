<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardModulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create();
        $this->regularUser = User::factory()->create();
    }

    private function createPermission(string $name): Permission
    {
        return Permission::create(['name' => $name]);
    }

    // -- Dashboard Home --

    public function test_dashboard_home_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/home');
        $response->assertStatus(200);
    }

    // -- General Settings --

    public function test_general_settings_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Website Settings'));

        $response = $this->actingAs($this->admin)->get('/dashboard/settings/general');
        $response->assertStatus(200);
    }

    public function test_general_settings_denied_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/dashboard/settings/general');
        $response->assertStatus(403);
    }

    // -- Finance Module --

    public function test_finance_overview_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Finances'));

        $response = $this->actingAs($this->admin)->get('/dashboard/finances/overview');
        $response->assertStatus(200);
    }

    public function test_finance_overview_denied_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/dashboard/finances/overview');
        $response->assertStatus(403);
    }

    public function test_finance_funds_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Finances'));

        $response = $this->actingAs($this->admin)->get('/dashboard/finances/funds');
        $response->assertStatus(200);
    }

    // -- Communication Module --

    public function test_sms_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Communication'));

        $response = $this->actingAs($this->admin)->get('/dashboard/communication/sms');
        $response->assertStatus(200);
    }

    // -- Events Module --

    public function test_events_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Events & Notices'));

        $response = $this->actingAs($this->admin)->get('/dashboard/events_and_notices/events');
        $response->assertStatus(200);
    }

    public function test_events_page_denied_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/dashboard/events_and_notices/events');
        $response->assertStatus(403);
    }

    // -- People Module --

    public function test_people_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View People'));

        $response = $this->actingAs($this->admin)->get('/dashboard/people/communities');
        $response->assertStatus(200);
    }

    // -- Users Module --

    public function test_users_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Users'));

        $response = $this->actingAs($this->admin)->get('/dashboard/users/all');
        $response->assertStatus(200);
    }

    public function test_users_page_denied_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/dashboard/users/all');
        $response->assertStatus(403);
    }

    // -- Roles Module --

    public function test_roles_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Roles'));

        $response = $this->actingAs($this->admin)->get('/dashboard/users/roles');
        $response->assertStatus(200);
    }

    public function test_roles_page_denied_without_permission(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/dashboard/users/roles');
        $response->assertStatus(403);
    }

    // -- Shop Module --

    public function test_shop_page_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/shop');
        $response->assertStatus(200);
    }

    // -- Articles Module --

    public function test_articles_page_accessible_with_permission(): void
    {
        $this->admin->givePermissionTo($this->createPermission('View Articles'));

        $response = $this->actingAs($this->admin)->get('/dashboard/articles');
        $response->assertStatus(200);
    }
}
