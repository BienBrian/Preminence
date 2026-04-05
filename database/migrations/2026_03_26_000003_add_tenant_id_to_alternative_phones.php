<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alternative_phones', function (Blueprint $table) {
            // Add tenant_id if it doesn't exist
            if (!Schema::hasColumn('alternative_phones', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->index()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alternative_phones', function (Blueprint $table) {
            $table->dropColumn(['tenant_id']);
        });
    }
};
