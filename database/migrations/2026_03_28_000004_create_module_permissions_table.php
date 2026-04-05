<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories for marketplace organization
        Schema::create('module_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Explicit dependency tracking
        Schema::create('module_dependencies', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('depends_on_key', 50);
            $table->boolean('is_required')->default(true);     // Hard vs soft dependency
            $table->string('min_version', 20)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['module_key', 'depends_on_key'], 'unique_dependency');
            $table->index(['depends_on_key'], 'idx_dependents');
        });
        
        // Module changelog for versioning
        Schema::create('module_changelogs', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('version', 20);
            $table->enum('type', ['major', 'minor', 'patch', 'security']);
            $table->text('changelog');
            $table->json('breaking_changes')->nullable();
            $table->json('migration_required')->nullable();
            $table->timestamp('released_at');
            $table->timestamps();
            
            $table->unique(['module_key', 'version'], 'unique_version');
            $table->index(['module_key', 'released_at'], 'idx_changelog');
        });

        // Module permissions for granular access control
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50);
            $table->string('permission_key', 100);           // 'finance.view_reports'
            $table->string('name', 100);                      // Display name
            $table->text('description')->nullable();
            $table->enum('level', ['basic', 'advanced', 'premium'])->default('basic');
            $table->boolean('is_premium')->default(false);    // Requires premium tier
            $table->json('requires_features')->nullable();    // Feature flags needed
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['module_key', 'permission_key'], 'unique_module_perm');
            $table->index(['module_key', 'is_active'], 'idx_module_perms');
        });
        
        // Pivot table for tenant-specific permission grants
        Schema::create('tenant_module_permission_grants', function (Blueprint $table) {
            $table->id();
            // Explicit FK names — auto-generated names exceed MySQL's 64-char identifier limit
            $table->unsignedBigInteger('tenant_module_subscription_id');
            $table->foreign('tenant_module_subscription_id', 'fk_perm_grant_subscription')
                  ->references('id')->on('tenant_module_subscriptions')->cascadeOnDelete();
            $table->foreignId('module_permission_id')
                  ->constrained('module_permissions')
                  ->cascadeOnDelete();
            $table->boolean('is_granted')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->json('conditions')->nullable();           // Conditional grants
            $table->timestamps();
            
            $table->unique(['tenant_module_subscription_id', 'module_permission_id'], 
                          'unique_permission_grant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_module_permission_grants');
        Schema::dropIfExists('module_permissions');
        Schema::dropIfExists('module_changelogs');
        Schema::dropIfExists('module_dependencies');
        Schema::dropIfExists('module_categories');
    }
};
