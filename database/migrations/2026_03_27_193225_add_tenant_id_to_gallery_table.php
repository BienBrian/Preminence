<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: Add tenant_id to gallery table
 * 
 * The gallery table was missing tenant_id column which is required
 * for the BelongsToTenant trait to work properly.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('gallery')) {
            return;
        }

        // Add tenant_id column if it doesn't exist
        if (!Schema::hasColumn('gallery', 'tenant_id')) {
            Schema::table('gallery', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')
                    ->default(1)
                    ->after('id');
            });

            // Add index and foreign key
            Schema::table('gallery', function (Blueprint $table) {
                $table->index('tenant_id', 'idx_gallery_tenant');
                $table->foreign('tenant_id', 'fk_gallery_tenant')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gallery') && Schema::hasColumn('gallery', 'tenant_id')) {
            Schema::table('gallery', function (Blueprint $table) {
                try { $table->dropForeign('fk_gallery_tenant'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_gallery_tenant'); } catch (\Exception $e) {}
                $table->dropColumn('tenant_id');
            });
        }
    }
};
