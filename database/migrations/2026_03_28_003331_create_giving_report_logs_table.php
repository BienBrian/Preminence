<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Giving Report Logs Table
 * 
 * Tracks all generated and emailed giving statements for audit purposes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giving_report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Recipient info
            $table->foreignId('user_id')->constrained('users')->comment('Report recipient');
            $table->foreignId('generated_by')->constrained('users')->comment('Admin who generated');
            
            // Report period
            $table->date('report_period_from');
            $table->date('report_period_to');
            $table->enum('period_type', ['monthly', 'quarterly', 'annual', 'custom'])->default('annual');
            
            // Report content
            $table->json('categories_included')->comment('Array of source IDs included');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            
            // Delivery info
            $table->enum('delivery_method', ['print', 'email', 'download', 'bulk'])->default('print');
            $table->string('email_sent_to', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            
            // Security
            $table->enum('password_protection_type', ['phone', 'id_number', 'custom_code', 'sms_otp', 'none']);
            $table->string('password_hint', 100)->nullable();
            
            // File info
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 200)->nullable();
            $table->string('file_format', 10)->default('pdf');
            $table->integer('file_size_bytes')->nullable();
            
            // Status tracking
            $table->enum('status', ['generated', 'sent', 'delivered', 'opened', 'failed'])->default('generated');
            $table->text('error_message')->nullable();
            
            // IP tracking for security
            $table->string('generated_from_ip', 45)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'status']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_report_logs');
    }
};
