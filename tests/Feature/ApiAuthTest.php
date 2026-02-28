<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Role::create(['name' => 'User']);
    }

    // -- Login --

    public function test_api_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'logintest@example.com',
            'password' => Hash::make('Password123!'),
        ]);
        $user->assignRole('User');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'logintest@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    public function test_api_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['errors']);
    }

    // -- Registration validation --

    public function test_api_register_rejects_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'username' => 'testuser456',
            'phone' => '254712345111',
            'referrer' => 'nonexistent',
            'email' => 'weakpwd@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms_and_conditions' => '1',
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('password', $response->json('errors'));
    }

    public function test_api_register_rejects_missing_terms(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'username' => 'testuser321',
            'phone' => '254712345333',
            'referrer' => 'someone',
            'email' => 'noterms@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('terms_and_conditions', $response->json('errors'));
    }

    public function test_api_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'username' => 'testuser789',
            'phone' => '254712345222',
            'referrer' => 'someone',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms_and_conditions' => '1',
        ]);

        $response->assertStatus(400);
        $this->assertArrayHasKey('email', $response->json('errors'));
    }

    // -- Authenticated endpoints --

    public function test_api_get_user_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/auth/dashboard/user');

        $response->assertStatus(401);
    }
}
