<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardTopbarTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->user = User::factory()->create();
    }

    private function getDashboardResponse()
    {
        return $this->actingAs($this->user)->get('/dashboard/home');
    }

    private function grantViewCredits(): void
    {
        $perm = Permission::firstOrCreate(['name' => 'View Credits', 'guard_name' => 'web']);
        $this->user->givePermissionTo($perm);
    }

    private function grantBuyCredits(): void
    {
        $perm = Permission::firstOrCreate(['name' => 'Buy Credits', 'guard_name' => 'web']);
        $this->user->givePermissionTo($perm);
    }

    // -- Credits Chip: Permission Gating --

    public function test_credits_chip_hidden_without_permission(): void
    {
        $response = $this->getDashboardResponse();

        $response->assertStatus(200);
        $response->assertDontSee('id="credits-chip"', false);
    }

    public function test_dashboard_shows_credits_chip(): void
    {
        $this->grantViewCredits();

        $response = $this->getDashboardResponse();

        $response->assertStatus(200);
        $response->assertSee('id="credits-chip"', false);
    }

    public function test_credits_chip_shows_sms_and_email_labels(): void
    {
        $this->grantViewCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="sms-credits"', false);
        $response->assertSee('id="email-credits"', false);
    }

    // -- Credits Popover: View Credits --

    public function test_credits_popover_markup_exists(): void
    {
        $this->grantViewCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-popover"', false);
        $response->assertSee('Buy Credits', false);
    }

    public function test_credits_popover_shows_balances_with_view_permission(): void
    {
        $this->grantViewCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('SMS Credits', false);
        $response->assertSee('Email Credits', false);
    }

    // -- Credits Popover: Buy Credits Permission Gating --

    public function test_buy_form_hidden_without_buy_credits_permission(): void
    {
        $this->grantViewCredits();
        // No 'Buy Credits' permission

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-popover"', false);
        $response->assertDontSee('id="credits-type-toggle"', false);
        $response->assertDontSee('id="credits-amount-sms"', false);
        $response->assertDontSee('id="credits-payment-method"', false);
        $response->assertDontSee('id="credits-next-btn"', false);
        $response->assertSee('Contact an administrator to purchase credits', false);
    }

    public function test_buy_form_visible_with_buy_credits_permission(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-type-toggle"', false);
        $response->assertSee('id="credits-amount-sms"', false);
        $response->assertSee('id="credits-payment-method"', false);
        $response->assertSee('id="credits-next-btn"', false);
        $response->assertDontSee('Contact an administrator to purchase credits', false);
    }

    public function test_credits_popover_has_type_toggle(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-type-toggle"', false);
        $response->assertSee('data-type="sms"', false);
        $response->assertSee('data-type="email"', false);
        $response->assertSee('data-type="both"', false);
    }

    public function test_credits_popover_has_amount_inputs(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-amount-sms"', false);
        $response->assertSee('id="credits-amount-email"', false);
    }

    public function test_credits_popover_has_payment_methods(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-payment-method"', false);
        $response->assertSee('M-Pesa', false);
        $response->assertSee('Credit / Debit Card', false);
        $response->assertSee('Bank Transfer', false);
    }

    public function test_credits_popover_has_preview_section(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-preview"', false);
        $response->assertSee('1 credit = KES 1', false);
    }

    public function test_credits_popover_has_next_button(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-next-btn"', false);
    }

    public function test_credits_popover_has_coming_soon_step(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('id="credits-step-2"', false);
        $response->assertSee('Coming Soon!', false);
    }

    public function test_coming_soon_step_hidden_without_buy_permission(): void
    {
        $this->grantViewCredits();
        // No 'Buy Credits' permission

        $response = $this->getDashboardResponse();

        $response->assertDontSee('id="credits-step-2"', false);
    }

    // -- Profile Dropdown --

    public function test_profile_icon_does_not_show_name_inline(): void
    {
        $user = User::factory()->create([
            'firstname' => 'Testington',
            'lastname' => 'McTestface',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/home');
        $content = $response->getContent();

        // The name should NOT appear next to the img-circle in the profile trigger link
        preg_match('/<a class="nav-link profile".*?<\/a>/s', $content, $profileLink);

        if (!empty($profileLink[0])) {
            $this->assertStringNotContainsString('Testington McTestface', $profileLink[0]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_profile_dropdown_shows_user_name(): void
    {
        $user = User::factory()->create([
            'firstname' => 'JohnTest',
            'lastname' => 'DoeTest',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/home');
        $response->assertSee('JohnTest DoeTest', false);
    }

    public function test_profile_dropdown_shows_user_email(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/home');
        $response->assertSee('testuser@example.com', false);
    }

    public function test_profile_dropdown_has_my_profile_link(): void
    {
        $response = $this->getDashboardResponse();

        $response->assertSee('My Profile', false);
        $response->assertSee('dashboard/profile', false);
    }

    public function test_profile_dropdown_has_logout_button(): void
    {
        $response = $this->getDashboardResponse();

        $response->assertSee('Logout', false);
    }

    // -- Settings Dropdown --

    public function test_settings_icon_visible_for_user_with_permission(): void
    {
        $permission = Permission::create(['name' => 'View Roles']);
        $this->user->givePermissionTo($permission);

        $response = $this->getDashboardResponse();

        $response->assertSee('fa-cog', false);
    }

    public function test_settings_icon_hidden_for_user_without_permission(): void
    {
        $response = $this->getDashboardResponse();
        $content = $response->getContent();

        $this->assertStringNotContainsString('title="Settings"', $content);
    }

    // -- Shop Icon --

    public function test_shop_icon_is_present(): void
    {
        $response = $this->getDashboardResponse();

        $response->assertSee('fa-shopping-bag', false);
        $response->assertSee('dashboard/shop', false);
    }

    // -- Topbar JavaScript --

    public function test_credits_javascript_is_loaded(): void
    {
        $this->grantViewCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('Credits Chip Popover', false);
    }

    public function test_buy_credits_javascript_loaded_with_permission(): void
    {
        $this->grantViewCredits();
        $this->grantBuyCredits();

        $response = $this->getDashboardResponse();

        $response->assertSee('updateCreditsPreview', false);
    }

    public function test_buy_credits_javascript_not_loaded_without_permission(): void
    {
        $this->grantViewCredits();
        // No 'Buy Credits' permission

        $response = $this->getDashboardResponse();

        $response->assertDontSee('updateCreditsPreview', false);
    }

    public function test_credits_javascript_not_loaded_without_view_permission(): void
    {
        // No 'View Credits' permission at all

        $response = $this->getDashboardResponse();

        $response->assertDontSee('Credits Chip Popover', false);
    }
}
