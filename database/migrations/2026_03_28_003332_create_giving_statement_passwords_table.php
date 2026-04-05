<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Giving Statement Passwords Table
 * 
 * Stores password information for generated reports.
 * Used for password verification and SMS OTP management.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('giving_statement_passwords')) {
            return;
        }

        Schema::create('giving_statement_passwords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('report_log_id')->constrained('giving_report_logs')->onDelete('cascade');
            
            // Password info
            $table->enum('password_type', ['phone', 'id_number', 'custom_code', 'sms_otp']);
            
            // For permanent passwords (phone, id_number, custom_code)
            $table->string('password_hash', 255)->nullable()->comment('Hashed password for verification');
            
            // For SMS OTP
            $table->string('sms_otp_code', 10)->nullable();
            $table->timestamp('sms_otp_expires_at')->nullable();
            $table->string('sms_sent_to', 20)->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            
            // Usage tracking
            $table->timestamp('used_at')->nullable()->comment('When password was first used');
            $table->integer('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->string('last_accessed_from_ip', 45)->nullable();
            
            // Security
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->unique('report_log_id');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'sms_otp_code']);
            $table->index('sms_otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_statement_passwords');
    }
};
