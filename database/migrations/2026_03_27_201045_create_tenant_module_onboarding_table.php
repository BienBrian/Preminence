<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant Module Onboarding Submissions
 * 
 * Stores onboarding form submissions from tenants requesting modules
 * including KYC data, uploaded documents, and approval status.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_module_onboarding')) {
            return;
        }

        Schema::create('tenant_module_onboarding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('module_key', 50);
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'needs_info'])->default('draft');
            $table->json('form_data')->nullable();      // KYC form responses
            $table->json('documents')->nullable();      // Uploaded file paths
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('review_notes')->nullable();
            $table->boolean('network_participation_opt_in')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            // No FK on module_key — modules table is created in a later migration (2026_03_28)
            // No FK on reviewed_by — super_admins.id relationship enforced at app level
            
            $table->index(['tenant_id', 'module_key']);
            $table->index(['status', 'submitted_at']);
            $table->unique(['tenant_id', 'module_key']); // Only one onboarding per tenant/module
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_module_onboarding');
    }
};
