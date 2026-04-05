<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // Module key must match the Module::* constants in App\Services\ModuleService
            $table->string('module', 50);   // 'people', 'finance', 'mpesa', 'sms', etc.

            $table->boolean('is_enabled')->default(false);

            // If true, superadmin has manually overridden the plan default
            // (e.g., enabling a Pro module for a Starter tenant for trial/support)
            $table->boolean('override_by_admin')->default(false);
            $table->unsignedBigInteger('overridden_by')->nullable(); // super_admin_id

            $table->foreignId('subscription_id')->nullable()->constrained('tenant_module_subscriptions')->nullOnDelete();
            
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'module'], 'unique_tenant_module');
            $table->index('tenant_id', 'idx_tenant_modules');
            $table->index('subscription_id', 'idx_tenant_modules_subscription');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
