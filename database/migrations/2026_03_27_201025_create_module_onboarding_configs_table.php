<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Onboarding Configuration
 *
 * Stores onboarding requirements and configuration for each module
 * including KYC forms, required documents, and tutorial content.
 *
 * Note: No FK on module_key — modules table is created in a later migration
 * (2026_03_28). Relationship enforced at application level.
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
            $table->json('required_documents')->nullable();
            $table->json('kyc_form_schema')->nullable();
            $table->json('tutorial_content')->nullable();
            $table->boolean('network_participation_enabled')->default(false);
            $table->text('approval_instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_onboarding_configs');
    }
};
