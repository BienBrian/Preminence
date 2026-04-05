<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add reference_category column to funds table for normalized reference types
        if (!Schema::hasColumn('funds', 'reference_category')) {
            Schema::table('funds', function (Blueprint $table) {
                $table->string('reference_category')->nullable()->after('description')
                    ->comment('Normalized reference type (e.g., offering, tithe, miliki)');
                $table->index('reference_category', 'idx_funds_ref_cat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('funds', 'reference_category')) {
            Schema::table('funds', function (Blueprint $table) {
                $table->dropIndex('idx_funds_ref_cat');
                $table->dropColumn('reference_category');
            });
        }
    }
};
