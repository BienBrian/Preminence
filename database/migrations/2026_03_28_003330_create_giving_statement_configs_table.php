<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Giving Statement Configuration Table
 * 
 * Stores tenant-specific settings for the giving statements module,
 * including password protection defaults and email templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('giving_statement_configs')) {
            return;
        }

        Schema::create('giving_statement_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Password protection settings
            $table->enum('default_password_type', ['phone', 'id_number', 'custom_code', 'sms_otp', 'none'])
                ->default('phone')
                ->comment('Default password protection method');
            $table->string('custom_password_code', 20)->nullable()
                ->comment('Custom code when password_type is custom_code');
            
            // Email template settings
            $table->string('email_template_subject', 200)
                ->default('Your {{ $churchName }} Giving Statement');
            $table->text('email_template_body')->nullable()
                ->comment('Custom email body template with variables');
            
            // Report content settings
            $table->boolean('include_thank_you_note')->default(true);
            $table->text('thank_you_note')->nullable()
                ->comment('Custom thank you message for reports');
            $table->string('church_header_text', 200)->nullable()
                ->comment('Custom text for report header');
            $table->string('report_title', 100)->default('Giving Statement');
            
            // Feature toggles
            $table->boolean('enable_bulk_generation')->default(true);
            $table->boolean('enable_email_delivery')->default(true);
            $table->boolean('enable_sms_otp')->default(false);
            
            // Report format settings
            $table->enum('report_format', ['pdf', 'excel', 'both'])->default('pdf');
            $table->tinyInteger('fiscal_year_start')->default(1)
                ->comment('Month number (1=January) when fiscal year starts');
            
            // PDF security settings
            $table->string('pdf_owner_password', 255)->nullable()
                ->comment('System password for PDF owner access');
            
            $table->timestamps();
            
            // Indexes
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_statement_configs');
    }
};
