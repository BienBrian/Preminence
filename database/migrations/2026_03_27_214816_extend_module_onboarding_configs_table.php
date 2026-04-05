<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend Module Onboarding Configs Table
 * 
 * Adds additional fields for richer onboarding configuration including
 * setup wizards, contextual help, and improved UX features.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_onboarding_configs', function (Blueprint $table) {
            // Extend onboarding_type enum to include new types
            // Note: We need to handle enum modification carefully
            
            // New fields for setup wizard support
            $table->json('setup_wizard_schema')->nullable()->after('kyc_form_schema')
                ->comment('Form schema for setup wizard steps');
            
            // Contextual help for first-time users
            $table->boolean('contextual_help_enabled')->default(false)->after('setup_wizard_schema')
                ->comment('Enable first-time tooltips and tours');
            $table->json('contextual_help_content')->nullable()->after('contextual_help_enabled')
                ->comment('Tooltip and tour definitions');
            
            // UX improvements
            $table->boolean('preview_enabled')->default(true)->after('contextual_help_content')
                ->comment('Allow users to preview module before activation');
            $table->unsignedSmallInteger('estimated_setup_time_minutes')->nullable()->after('preview_enabled')
                ->comment('Estimated time to complete onboarding');
            
            // Custom messaging
            $table->text('welcome_message')->nullable()->after('estimated_setup_time_minutes')
                ->comment('Custom welcome message shown at start');
            $table->text('completion_message')->nullable()->after('welcome_message')
                ->comment('Message shown after successful onboarding');
            
            // Post-onboarding behavior
            $table->boolean('auto_redirect_to_module')->default(true)->after('completion_message')
                ->comment('Auto-redirect to module after onboarding');
            $table->string('video_url', 500)->nullable()->after('auto_redirect_to_module')
                ->comment('Explainer video URL');
            
            // Help documentation
            $table->string('documentation_url', 500)->nullable()->after('video_url')
                ->comment('Link to detailed documentation');
            
            // Template reference
            $table->string('template_key', 50)->nullable()->after('documentation_url')
                ->comment('Reference to predefined template used');
            
            // Status tracking
            $table->boolean('is_configured')->default(false)->after('template_key')
                ->comment('Whether onboarding has been configured by admin');
            $table->timestamp('configured_at')->nullable()->after('is_configured');
        });
        
        // Update existing enum to include new types
        // Note: Laravel doesn't support enum modification directly, so we use raw SQL
        DB::statement("ALTER TABLE module_onboarding_configs 
            MODIFY COLUMN onboarding_type ENUM('instant', 'guided', 'setup_wizard', 'kyc', 'none') 
            DEFAULT 'none'");
    }

    public function down(): void
    {
        Schema::table('module_onboarding_configs', function (Blueprint $table) {
            $table->dropColumn([
                'setup_wizard_schema',
                'contextual_help_enabled',
                'contextual_help_content',
                'preview_enabled',
                'estimated_setup_time_minutes',
                'welcome_message',
                'completion_message',
                'auto_redirect_to_module',
                'video_url',
                'documentation_url',
                'template_key',
                'is_configured',
                'configured_at',
            ]);
        });
        
        // Revert enum to original values
        DB::statement("ALTER TABLE module_onboarding_configs 
            MODIFY COLUMN onboarding_type ENUM('kyc', 'guided', 'none') 
            DEFAULT 'none'");
    }
};
