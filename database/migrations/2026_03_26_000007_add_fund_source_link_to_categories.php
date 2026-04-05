<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add fund_source_id to summary_categories to link to sources table
        if (!Schema::hasColumn('summary_categories', 'fund_source_id')) {
            Schema::table('summary_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('fund_source_id')->nullable()->after('id');
                $table->index('fund_source_id', 'idx_summary_cat_fund_source');
            });
        }

        // Add is_default field to track system-default categories from fund sources
        if (!Schema::hasColumn('summary_categories', 'is_default')) {
            Schema::table('summary_categories', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('summary_categories', 'fund_source_id')) {
            Schema::table('summary_categories', function (Blueprint $table) {
                $table->dropIndex('idx_summary_cat_fund_source');
                $table->dropColumn('fund_source_id');
            });
        }
        if (Schema::hasColumn('summary_categories', 'is_default')) {
            Schema::table('summary_categories', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
