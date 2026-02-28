<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_user_can_be_created_via_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_has_required_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('firstname', $fillable);
        $this->assertContains('lastname', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('phone', $fillable);
    }

    public function test_user_hides_sensitive_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_casts_dates_correctly(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2026-01-01 00:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_can_have_role(): void
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::factory()->create();

        $user->assignRole('Admin');

        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_user_can_have_permission(): void
    {
        $permission = Permission::create(['name' => 'View Finances']);
        $user = User::factory()->create();

        $user->givePermissionTo('View Finances');

        $this->assertTrue($user->can('View Finances'));
    }

    public function test_user_can_have_tags(): void
    {
        // Create tags table if it exists via migration
        if (\Schema::hasTable('tags') && \Schema::hasTable('tag_user')) {
            $user = User::factory()->create();
            $tag = Tag::create(['name' => 'Youth']);

            $user->tags()->attach($tag);

            $this->assertTrue($user->tags->contains($tag));
        } else {
            $this->markTestSkipped('Tags tables not available.');
        }
    }

    public function test_inactive_user_factory_state(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertEquals(0, $user->status);
    }

    public function test_must_change_password_factory_state(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->assertTrue((bool) $user->must_change_password);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create();

        // Factory uses Hash::make, so the stored password should not be plaintext
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(\Hash::check('Password123!', $user->password));
    }
}
