<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->string('category', 20)->default('manual')->after('message');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->string('category', 20)->default('manual')->after('message');
        });

        // Backfill existing mpesa automated messages
        DB::table('sms')
            ->where('message', 'LIKE', '%Thank you for honouring the Lord with your finances%')
            ->orWhere('message', 'LIKE', '%Thank you%for supporting the ministry%')
            ->update(['category' => 'mpesa']);

        // Backfill birthday messages
        DB::table('sms')
            ->where('message', 'LIKE', '%Happy Birthday%')
            ->update(['category' => 'birthday']);

        // Backfill pledge messages
        DB::table('sms')
            ->where('message', 'LIKE', '%pledg%')
            ->where('category', 'manual')
            ->update(['category' => 'pledge']);
    }

    public function down(): void
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
