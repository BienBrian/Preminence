<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update plans table with marketplace mode
        Schema::table('plans', function (Blueprint $table) {
            $table->enum('module_mode', ['whitelist', 'blacklist', 'marketplace'])
                  ->default('whitelist')
                  ->after('features');
            $table->boolean('allow_addon_purchases')
                  ->default(false)
                  ->after('module_mode');
            $table->integer('max_addons')
                  ->default(0)
                  ->comment('0 = unlimited')
                  ->after('allow_addon_purchases');
            $table->boolean('allow_downgrades')
                  ->default(true)
                  ->after('max_addons');
            $table->json('marketplace_settings')
                  ->nullable()
                  ->comment('Plan-specific marketplace configuration')
                  ->after('allow_downgrades');
        });
        
        // Link tenant_modules to subscriptions
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->foreignId('subscription_id')
                  ->nullable()
                  ->after('module')
                  ->constrained('tenant_module_subscriptions')
                  ->nullOnDelete();
            $table->enum('installed_via', ['plan', 'marketplace', 'admin', 'migration'])
                  ->default('plan')
                  ->after('subscription_id');
            $table->string('source_subscription_key', 50)
                  ->nullable()
                  ->comment('Reference to original subscription for tracking')
                  ->after('installed_via');
            
            $table->index(['subscription_id'], 'idx_tenant_module_subscription');
            $table->index(['installed_via'], 'idx_installed_via');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'installed_via', 'source_subscription_key']);
        });
        
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'module_mode', 
                'allow_addon_purchases', 
                'max_addons',
                'allow_downgrades',
                'marketplace_settings'
            ]);
        });
    }
};
