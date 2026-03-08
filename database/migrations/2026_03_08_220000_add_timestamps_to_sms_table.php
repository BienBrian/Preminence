<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: Add timestamps (created_at, updated_at) to sms table
 * 
 * The sms table was missing the timestamp columns required by Eloquent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms')) {
            return;
        }

        // Add timestamps if they don't exist
        if (!Schema::hasColumn('sms', 'created_at')) {
            Schema::table('sms', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->after('sent');
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sms') && Schema::hasColumn('sms', 'created_at')) {
            Schema::table('sms', function (Blueprint $table) {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
