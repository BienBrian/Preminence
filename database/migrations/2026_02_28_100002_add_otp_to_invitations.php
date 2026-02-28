<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add OTP fields to invitations table for the new onboarding flow
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('phone_otp', 6)->nullable()->after('phone');
            $table->string('email_otp', 6)->nullable()->after('email');
            $table->timestamp('otp_expires_at')->nullable()->after('email_otp');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['phone_otp', 'email_otp', 'otp_expires_at']);
        });
    }
};
