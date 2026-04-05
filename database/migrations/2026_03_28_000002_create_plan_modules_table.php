<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plan_modules')) {
            return;
        }

        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 50);
            
            // Inclusion & Availability
            $table->boolean('is_included')->default(false)->index();      // In plan price
            $table->boolean('is_available')->default(true);                // Can purchase add-on
            $table->boolean('is_featured')->default(false);                // Featured on plan page
            
            // Pricing Overrides (NULL = use module default)
            $table->decimal('price_monthly_override', 10, 2)->nullable();
            $table->decimal('price_yearly_override', 10, 2)->nullable();
            $table->decimal('setup_fee_override', 10, 2)->nullable();
            
            // Limits Override
            $table->json('limits_override')->nullable();
            
            // Trial Configuration
            $table->integer('trial_days')->default(0);                     // Override module default
            $table->boolean('extend_existing_trial')->default(false);      // Add to existing trial
            
            // Display Configuration
            $table->json('plan_highlights')->nullable();                   // Plan-specific highlights
            $table->string('plan_badge', 50)->nullable();                  // 'Popular', 'New', etc.
            
            // Configuration
            $table->json('configuration')->nullable();                     // Plan-specific module config
            
            $table->timestamps();
            
            // Constraints & Indexes
            $table->unique(['plan_id', 'module_key'], 'unique_plan_module');
            $table->index(['plan_id', 'is_included', 'is_available'], 'idx_plan_modules_lookup');
            
            // Foreign key to modules (soft reference for flexibility)
            // We use string key rather than foreignId for loose coupling
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_modules');
    }
};
