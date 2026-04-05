<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Module Onboarding Steps Table
 * 
 * Stores individual steps for complex multi-step onboarding flows.
 * Allows versioned, reorderable steps with rich content support.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_onboarding_steps')) {
            return;
        }

        Schema::create('module_onboarding_steps', function (Blueprint $table) {
            $table->id();
            
            // Relationship to onboarding config
            $table->foreignId('module_onboarding_config_id')
                ->constrained('module_onboarding_configs')
                ->onDelete('cascade');
            
            // Step identification
            $table->unsignedSmallInteger('step_number')->default(1)
                ->comment('Display order of the step');
            $table->string('step_key', 50)
                ->comment('Unique identifier for this step within the onboarding');
            
            // Content
            $table->string('title', 200)
                ->comment('Step title displayed to user');
            $table->text('description')->nullable()
                ->comment('Step description/subtitle');
            $table->text('content')->nullable()
                ->comment('Main content (HTML supported)');
            
            // Content type determines how step is rendered
            $table->enum('content_type', [
                'info',           // Informational content only
                'form',           // Contains form fields
                'video',          // Video tutorial
                'document_upload', // Document upload required
                'confirmation',   // Confirmation/acknowledgment
                'completion',     // Final success step
            ])->default('info');
            
            // Rich media
            $table->string('video_url', 500)->nullable()
                ->comment('Video tutorial URL for this step');
            $table->string('image_url', 500)->nullable()
                ->comment('Illustrative image for this step');
            $table->string('icon', 50)->nullable()
                ->comment('Bootstrap icon class (e.g., bi-check-circle)');
            
            // Form configuration (when content_type is 'form')
            $table->json('form_schema')->nullable()
                ->comment('Form field definitions for this step');
            
            // Document requirements (when content_type is 'document_upload')
            $table->json('document_config')->nullable()
                ->comment('Document upload configuration');
            
            // Step behavior
            $table->boolean('is_required')->default(true)
                ->comment('Step must be completed');
            $table->boolean('is_skippable')->default(false)
                ->comment('User can skip this step');
            $table->boolean('allow_back')->default(true)
                ->comment('User can go back to previous step');
            
            // Conditional logic
            $table->json('show_conditions')->nullable()
                ->comment('Conditions to show this step (JSON logic)');
            $table->json('next_step_logic')->nullable()
                ->comment('Dynamic next step routing based on answers');
            
            // Progress tracking
            $table->unsignedSmallInteger('estimated_minutes')->nullable()
                ->comment('Estimated time for this step');
            
            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Step is active and visible');
            
            $table->timestamps();
            
            // Indexes — explicit names to stay under MySQL's 64-char identifier limit
            $table->unique(['module_onboarding_config_id', 'step_key'], 'uq_onboarding_steps_config_key');
            $table->index(['module_onboarding_config_id', 'step_number'], 'idx_onboarding_steps_config_num');
            $table->index('content_type', 'idx_onboarding_steps_content_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_onboarding_steps');
    }
};
