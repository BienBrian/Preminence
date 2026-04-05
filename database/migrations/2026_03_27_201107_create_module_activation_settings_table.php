<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Activation Settings
 * 
 * Controls how tenants can activate modules - whether they need approval,
 * can self-activate, or are auto-approved based on plan tier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_activation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50)->unique();
            $table->boolean('tenant_can_self_activate')->default(false);
            $table->boolean('requires_superadmin_approval')->default(false);
            $table->json('auto_approve_for_plans')->nullable(); // ['enterprise', 'premium']
            $table->string('minimum_plan_tier')->nullable(); // NULL = any plan
            $table->boolean('allow_trial')->default(true);
            $table->integer('trial_days')->default(14);
            $table->json('activation_messages')->nullable(); // Custom messages per status
            $table->timestamps();
            
            // No FK on module_key — modules table is created in a later migration (2026_03_28)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_activation_settings');
    }
};
