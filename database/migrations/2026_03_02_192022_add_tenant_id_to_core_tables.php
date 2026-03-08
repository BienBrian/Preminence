<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users list
        Schema::table('users', function (Blueprint $table) {
            // Add tenant_id AFTER id
            $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
            // Drop old unique constraints if any exist here that would break multi-tenant (e.g. email unique globally).
            // Usually, users.email is globally unique in a single tenant app. 
            // In SaaS, the same email might exist across multiple tenants, so we make it unique per tenant.
            // But doing an index drop safely requires knowing if it exists. We'll handle index changes carefully.
        });
        
        // Add foreign key and composite unique index
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            // If the table previously had a unique email constraint, drop it and create a composite one.
            // Using raw DB statement to safely drop if exists (MySQL specific but safer for existing databases)
            // It's safer to just let the developer drop it manually if it fails, or try/catch.
        });
        
        // For existing databases, let's safely try to drop the old email unique index
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
            });
        } catch (\Exception $e) {
            // Index doesn't exist or already dropped
        }

        Schema::table('users', function (Blueprint $table) {
            // New unique constraint: one email per tenant
            $table->unique(['tenant_id', 'email'], 'users_tenant_email_unique');
        });

        // 2. Settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 3. Integrations table
        Schema::table('integrations', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
        });
        Schema::table('integrations', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
        
        // 4. Update the tenant owner now that users have tenant_id
        // Find the first user of tenant 1 (Happy Church) and make them the owner
        $firstUser = DB::table('users')->where('tenant_id', 1)->orderBy('id')->first();
        if ($firstUser) {
            DB::table('tenants')->where('id', 1)->update(['owner_user_id' => $firstUser->id]);
        }
        
        // Add FK for owner_user_id on tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
        });
        
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('users_tenant_email_unique');
            $table->dropColumn('tenant_id');
            // We don't restore the old uk_email index natively here to keep it simple
        });
    }
};
