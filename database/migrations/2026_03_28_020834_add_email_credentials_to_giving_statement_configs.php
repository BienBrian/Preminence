<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add email credentials to giving statement configs
 * 
 * Allows tenants to configure their own SMTP settings for sending
 * giving statement emails, independent of system-wide email settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giving_statement_configs', function (Blueprint $table) {
            // Email driver selection
            $table->enum('email_driver', ['smtp', 'mailgun', 'sendgrid', 'postmark', 'ses', 'log', 'default'])
                ->default('default')
                ->after('enable_email_delivery')
                ->comment('Email driver to use for sending giving statements');
            
            // SMTP Settings
            $table->string('smtp_host', 255)->nullable()->after('email_driver');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username', 255)->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username');
            $table->enum('smtp_encryption', ['tls', 'ssl', 'none'])->default('tls')->after('smtp_password');
            
            // From address settings
            $table->string('email_from_address', 255)->nullable()->after('smtp_encryption');
            $table->string('email_from_name', 255)->nullable()->after('email_from_address');
            
            // Reply-to settings
            $table->string('email_reply_to', 255)->nullable()->after('email_from_name');
            
            // Service-specific settings (encrypted)
            $table->text('email_service_key')->nullable()->after('email_reply_to')
                ->comment('API key for services like Mailgun, SendGrid, etc.');
            $table->text('email_service_secret')->nullable()->after('email_service_key')
                ->comment('Secret key for email services');
            $table->string('email_service_domain', 255)->nullable()->after('email_service_secret')
                ->comment('Domain for Mailgun/Postmark');
            $table->string('email_service_region', 50)->nullable()->after('email_service_domain')
                ->comment('Region for AWS SES');
            
            // Test settings
            $table->string('test_email_address', 255)->nullable()
                ->comment('Email address for testing configuration');
            $table->timestamp('email_config_tested_at')->nullable()
                ->comment('Last time email config was tested successfully');
            $table->text('email_config_test_error')->nullable()
                ->comment('Error message from last failed test');
        });
    }

    public function down(): void
    {
        Schema::table('giving_statement_configs', function (Blueprint $table) {
            $table->dropColumn([
                'email_driver',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'email_from_address',
                'email_from_name',
                'email_reply_to',
                'email_service_key',
                'email_service_secret',
                'email_service_domain',
                'email_service_region',
                'test_email_address',
                'email_config_tested_at',
                'email_config_test_error',
            ]);
        });
    }
};
