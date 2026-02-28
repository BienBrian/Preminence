<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // -- Login Tests --

    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard/home');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword99!',
        ]);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
    }

    public function test_login_with_empty_fields_stays_unauthenticated(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $this->assertGuest();
    }

    // -- Logout Tests --

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    // -- Force Password Change Middleware --

    public function test_user_with_must_change_password_is_redirected(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get('/dashboard/home');

        $response->assertRedirect('/password/force-change');
    }

    public function test_user_without_must_change_password_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard/home');

        $response->assertStatus(200);
    }

    public function test_force_password_change_page_renders(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get('/password/force-change');

        $response->assertStatus(200);
    }

    public function test_user_can_change_forced_password(): void
    {
        $user = User::factory()->mustChangePassword()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->post('/password/force-change', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertRedirect('/dashboard/home');

        $user->refresh();
        $this->assertFalse((bool) $user->must_change_password);
        $this->assertTrue(Hash::check('NewPassword456!', $user->password));
    }

    public function test_force_password_change_rejects_wrong_current_password(): void
    {
        $user = User::factory()->mustChangePassword()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->post('/password/force-change', [
            'current_password' => 'WrongCurrentPwd1!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertSessionHasErrors();
    }

    // -- Unauthenticated Access --

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard/home');

        $response->assertRedirect('/login');
    }
}
