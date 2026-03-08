<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: Add tenant_id to galleries table
 * 
 * The galleries table was missing from the add_tenant_id_to_all_tables migration.
 * This migration adds the missing column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('galleries')) {
            return;
        }

        // Add tenant_id column if it doesn't exist
        if (!Schema::hasColumn('galleries', 'tenant_id')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')
                    ->default(1)
                    ->after('id');
            });

            // Add index and foreign key
            Schema::table('galleries', function (Blueprint $table) {
                $table->index('tenant_id', 'idx_galleries_tenant');
                $table->foreign('tenant_id', 'fk_galleries_tenant')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('galleries') && Schema::hasColumn('galleries', 'tenant_id')) {
            Schema::table('galleries', function (Blueprint $table) {
                try { $table->dropForeign('fk_galleries_tenant'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_galleries_tenant'); } catch (\Exception $e) {}
                $table->dropColumn('tenant_id');
            });
        }
    }
};
