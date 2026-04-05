<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add summary_category_id to reference_type_mappings
        if (!Schema::hasColumn('reference_type_mappings', 'summary_category_id')) {
            Schema::table('reference_type_mappings', function (Blueprint $table) {
                $table->unsignedBigInteger('summary_category_id')->nullable()->after('mapped_ref');
                $table->index('summary_category_id', 'idx_refmap_category');
                $table->foreign('summary_category_id', 'fk_refmap_category')
                    ->references('id')
                    ->on('summary_categories')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reference_type_mappings', 'summary_category_id')) {
            Schema::table('reference_type_mappings', function (Blueprint $table) {
                $table->dropForeign('fk_refmap_category');
                $table->dropIndex('idx_refmap_category');
                $table->dropColumn('summary_category_id');
            });
        }
    }
};
