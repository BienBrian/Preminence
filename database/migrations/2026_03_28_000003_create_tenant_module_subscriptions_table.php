<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_module_subscriptions')) {
            return;
        }

        Schema::create('tenant_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 50);
            
            // Installation Lifecycle Status
            $table->enum('status', [
                'pending',           // Queued for installation
                'installing',        // Currently installing
                'active',            // Fully operational
                'suspended',         // Payment issue or admin action
                'paused',            // Temporarily disabled
                'uninstalling',      // In progress
                'uninstalled',       // Cleaned up
                'failed',            // Installation failed
            ])->default('pending')->index();
            
            // Billing Information
            $table->enum('billing_type', [
                'plan_included',
                'addon_monthly',
                'addon_yearly',
                'one_time',
                'trial',
                'complimentary',
            ]);
            $table->decimal('price', 10, 2)->nullable();       // Price at subscription time
            $table->string('currency', 3)->default('KES');
            $table->string('idempotency_key', 100)->unique()->nullable(); // For payment safety
            
            // Dates
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            
            // Installation Tracking
            $table->foreignId('installed_by')->nullable()->constrained('users');
            $table->string('version_installed', 20)->nullable();
            $table->json('installation_log')->nullable();       // Step-by-step log
            $table->text('installation_error')->nullable();     // If failed
            
            // Settings & Configuration
            $table->json('settings')->nullable();               // Module-specific settings
            $table->json('limits')->nullable();                 // Applied limits
            $table->json('features_enabled')->nullable();       // Feature flags state
            
            // Usage Tracking
            $table->bigInteger('usage_count')->default(0);      // Generic usage counter
            $table->json('usage_metrics')->nullable();          // Module-specific metrics
            $table->timestamp('last_used_at')->nullable();
            
            // Cancellation Tracking
            $table->text('cancellation_reason')->nullable();
            $table->json('cancellation_feedback')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('super_admins');
            $table->foreignId('updated_by')->nullable()->constrained('super_admins');
            $table->timestamps();
            
            // Indexes for Common Queries
            $table->unique(['tenant_id', 'module_key'], 'unique_tenant_module');
            $table->index(['tenant_id', 'status'], 'idx_tenant_status');
            $table->index(['next_billing_at', 'status'], 'idx_billing_schedule');
            $table->index(['status', 'created_at'], 'idx_pending_installs');
            $table->index(['trial_ends_at'], 'idx_trial_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_module_subscriptions');
    }
};
