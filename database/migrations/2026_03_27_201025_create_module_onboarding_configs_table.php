<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Onboarding Configuration
 * 
 * Stores onboarding requirements and configuration for each module
 * including KYC forms, required documents, and tutorial content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_onboarding_configs', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50)->unique();
            $table->enum('onboarding_type', ['kyc', 'guided', 'none'])->default('none');
            $table->boolean('requires_approval')->default(false);
            $table->json('required_documents')->nullable(); // ['certificate', 'bank_details', 'board_resolution']
            $table->json('kyc_form_schema')->nullable();    // Dynamic form fields
            $table->json('tutorial_content')->nullable();   // Steps, videos, content
            $table->boolean('network_participation_enabled')->default(false);
            $table->text('approval_instructions')->nullable(); // Instructions for SuperAdmin
            $table->timestamps();
            
            $table->foreign('module_key')
                ->references('key')
                ->on('modules')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_onboarding_configs');
    }
};
