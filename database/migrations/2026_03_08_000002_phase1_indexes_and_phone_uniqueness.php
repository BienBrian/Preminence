<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: Per-tenant phone uniqueness + composite performance indexes.
 *
 * - Drop the global phone unique on users, add (tenant_id, phone) composite unique
 * - Add composite indexes for common tenant-scoped queries:
 *   users: (tenant_id, phone), (tenant_id, email), (tenant_id, status)
 *   funds: (tenant_id, created_at)
 *   sms:   (tenant_id, created_at)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ─────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Drop the global phone unique if it exists
            try { $table->dropUnique('users_phone_unique'); } catch (\Exception $e) {}
            // Per-tenant phone uniqueness
            $table->unique(['tenant_id', 'phone'], 'users_tenant_phone_unique');
            // Composite lookup indexes
            $table->index(['tenant_id', 'phone'],  'idx_users_tenant_phone');
            $table->index(['tenant_id', 'email'],  'idx_users_tenant_email');
            $table->index(['tenant_id', 'status'], 'idx_users_tenant_status');
        });

        // ── funds ─────────────────────────────────────────────────────────────
        // Only add if tenant_id already exists (it was added in the earlier migration)
        if (Schema::hasColumn('funds', 'tenant_id')) {
            Schema::table('funds', function (Blueprint $table) {
                $table->index(['tenant_id', 'created_at'], 'idx_funds_tenant_created');
            });
        }

        // ── sms ───────────────────────────────────────────────────────────────
        if (Schema::hasColumn('sms', 'tenant_id')) {
            Schema::table('sms', function (Blueprint $table) {
                $table->index(['tenant_id', 'created_at'], 'idx_sms_tenant_created');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropUnique('users_tenant_phone_unique'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_users_tenant_phone');   } catch (\Exception $e) {}
            try { $table->dropIndex('idx_users_tenant_email');   } catch (\Exception $e) {}
            try { $table->dropIndex('idx_users_tenant_status');  } catch (\Exception $e) {}
        });

        if (Schema::hasColumn('funds', 'tenant_id')) {
            Schema::table('funds', function (Blueprint $table) {
                try { $table->dropIndex('idx_funds_tenant_created'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasColumn('sms', 'tenant_id')) {
            Schema::table('sms', function (Blueprint $table) {
                try { $table->dropIndex('idx_sms_tenant_created'); } catch (\Exception $e) {}
            });
        }
    }
};
