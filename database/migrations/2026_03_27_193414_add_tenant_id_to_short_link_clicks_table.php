<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: Add tenant_id to short_link_clicks table
 * 
 * This table is accessed through ShortLink relationship but needs
 * tenant_id for proper isolation in queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('short_link_clicks')) {
            return;
        }

        // Add tenant_id column if it doesn't exist
        if (!Schema::hasColumn('short_link_clicks', 'tenant_id')) {
            Schema::table('short_link_clicks', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')
                    ->default(1)
                    ->after('id');
            });

            // Add index
            Schema::table('short_link_clicks', function (Blueprint $table) {
                $table->index('tenant_id', 'idx_short_link_clicks_tenant');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('short_link_clicks') && Schema::hasColumn('short_link_clicks', 'tenant_id')) {
            Schema::table('short_link_clicks', function (Blueprint $table) {
                try { $table->dropIndex('idx_short_link_clicks_tenant'); } catch (\Exception $e) {}
                $table->dropColumn('tenant_id');
            });
        }
    }
};
