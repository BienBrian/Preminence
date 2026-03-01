<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index funds table for faster MPESA summary queries
        Schema::table('funds', function (Blueprint $table) {
            try {
                $table->index(['source', 'user_id'], 'funds_source_user_id_index');
            } catch (\Exception $e) {
                // Index may already exist
            }
        });

        // Add phone column to sms_recipients for tracking non-registered recipients
        Schema::table('sms_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('sms_recipients', 'phone')) {
                $table->string('phone', 20)->nullable()->after('recipients');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            try {
                $table->dropIndex('funds_source_user_id_index');
            } catch (\Exception $e) {}
        });

        Schema::table('sms_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('sms_recipients', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
