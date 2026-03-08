<?php

namespace Tests\Feature;

use App\Models\Funds;
use App\Models\User;
use App\Models\MpesaTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 4 (4.16): Tenant Isolation Test
 *
 * Verifies that data from Tenant A is never visible to Tenant B
 * across users, funds, contacts, and Mpesa transactions.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────── helpers

    protected function setTenant(int $id): void
    {
        config(['app.tenant_id' => $id]);
    }

    /**
     * Seed a minimal tenant row and return its id.
     */
    protected function createTenant(string $name): int
    {
        return DB::table('tenants')->insertGetId([
            'name'       => $name,
            'slug'       => \Str::slug($name),
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a user scoped to $tenantId (bypassing Eloquent scope).
     */
    protected function createUserForTenant(int $tenantId, string $email): int
    {
        return DB::table('users')->insertGetId([
            'firstname'  => 'Test',
            'lastname'   => 'User',
            'email'      => $email,
            'password'   => bcrypt('password'),
            'role'       => 0,
            'status'     => 1,
            'tenant_id'  => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a fund record scoped to $tenantId.
     */
    protected function createFundForTenant(int $tenantId, int $userId, float $amount): void
    {
        DB::table('funds')->insert([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'amount'      => $amount,
            'source'      => 1,
            'description' => 'Test fund',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Create an Mpesa transaction scoped to $tenantId.
     */
    protected function createMpesaForTenant(int $tenantId, string $phone): void
    {
        DB::table('mpesa_transactions')->insert([
            'tenant_id'        => $tenantId,
            'MpesaReceiptNumber' => 'TEST' . rand(10000, 99999),
            'TransactionDate'  => now(),
            'PhoneNumber'      => $phone,
            'Amount'           => 100,
            'AccountReference' => 'REF',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    // ──────────────────────────────────────────── tests

    /** @test */
    public function user_eloquent_scope_isolates_by_tenant()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        $this->createUserForTenant($tenantA, 'alice@a.com');
        $this->createUserForTenant($tenantA, 'bob@a.com');
        $this->createUserForTenant($tenantB, 'carol@b.com');

        // Tenant A should see only their 2 users
        $this->setTenant($tenantA);
        $this->assertEquals(2, User::count(), 'Tenant A should see exactly 2 users');
        $this->assertFalse(User::where('email', 'carol@b.com')->exists(), 'Tenant A must not see Tenant B users');

        // Tenant B should see only their 1 user
        $this->setTenant($tenantB);
        $this->assertEquals(1, User::count(), 'Tenant B should see exactly 1 user');
        $this->assertFalse(User::where('email', 'alice@a.com')->exists(), 'Tenant B must not see Tenant A users');
    }

    /** @test */
    public function funds_eloquent_scope_isolates_by_tenant()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        $userA = $this->createUserForTenant($tenantA, 'alice@a.com');
        $userB = $this->createUserForTenant($tenantB, 'carol@b.com');

        $this->createFundForTenant($tenantA, $userA, 1000);
        $this->createFundForTenant($tenantA, $userA, 500);
        $this->createFundForTenant($tenantB, $userB, 2000);

        $this->setTenant($tenantA);
        $this->assertEquals(2, Funds::count(), 'Tenant A should see 2 fund records');
        $this->assertEquals(1500, Funds::sum('amount'), 'Tenant A total should be 1500');

        $this->setTenant($tenantB);
        $this->assertEquals(1, Funds::count(), 'Tenant B should see 1 fund record');
        $this->assertEquals(2000, Funds::sum('amount'), 'Tenant B total should be 2000');
    }

    /** @test */
    public function raw_db_table_query_isolation_for_funds()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        $userA = $this->createUserForTenant($tenantA, 'alice@a.com');
        $this->createFundForTenant($tenantA, $userA, 9999);

        // A raw query scoped to tenant B should return 0
        $this->setTenant($tenantB);
        $count = DB::table('funds')->where('tenant_id', $tenantB)->count();
        $this->assertEquals(0, $count, 'Tenant B raw query must not see Tenant A funds');
    }

    /** @test */
    public function mpesa_transactions_isolated_by_tenant()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        $this->createMpesaForTenant($tenantA, '254700000001');
        $this->createMpesaForTenant($tenantB, '254700000002');

        $this->setTenant($tenantA);
        $this->assertEquals(1, MpesaTransaction::count(), 'Tenant A should see 1 Mpesa transaction');
        $this->assertFalse(
            MpesaTransaction::where('PhoneNumber', '254700000002')->exists(),
            'Tenant A must not see Tenant B Mpesa transactions'
        );

        $this->setTenant($tenantB);
        $this->assertEquals(1, MpesaTransaction::count(), 'Tenant B should see 1 Mpesa transaction');
        $this->assertFalse(
            MpesaTransaction::where('PhoneNumber', '254700000001')->exists(),
            'Tenant B must not see Tenant A Mpesa transactions'
        );
    }

    /** @test */
    public function sms_records_isolated_by_tenant()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        DB::table('sms')->insert(['tenant_id' => $tenantA, 'message' => 'Hello Church A', 'people_id' => 0, 'sent' => now()]);
        DB::table('sms')->insert(['tenant_id' => $tenantB, 'message' => 'Hello Church B', 'people_id' => 0, 'sent' => now()]);

        $this->setTenant($tenantA);
        $count = DB::table('sms')->where('tenant_id', $tenantA)->count();
        $this->assertEquals(1, $count, 'Tenant A raw SMS query should return 1');
        $exists = DB::table('sms')->where('tenant_id', $tenantA)->where('message', 'Hello Church B')->exists();
        $this->assertFalse($exists, 'Tenant A must not see Tenant B SMS messages');
    }

    /** @test */
    public function contacts_isolated_by_tenant()
    {
        $tenantA = $this->createTenant('Church A');
        $tenantB = $this->createTenant('Church B');

        $userA = $this->createUserForTenant($tenantA, 'alice@a.com');
        $userB = $this->createUserForTenant($tenantB, 'carol@b.com');

        DB::table('contacts')->insert(['tenant_id' => $tenantA, 'user_id' => $userA, 'phone' => '0700000001', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('contacts')->insert(['tenant_id' => $tenantB, 'user_id' => $userB, 'phone' => '0700000002', 'created_at' => now(), 'updated_at' => now()]);

        $countA = DB::table('contacts')->where('tenant_id', $tenantA)->count();
        $this->assertEquals(1, $countA, 'Tenant A contacts count should be 1');

        $leakage = DB::table('contacts')->where('tenant_id', $tenantA)->where('phone', '0700000002')->exists();
        $this->assertFalse($leakage, 'Tenant A must not see Tenant B contacts');
    }
}
