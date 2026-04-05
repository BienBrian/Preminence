<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Module Identification
            $table->string('key', 50)->unique();           // 'finance', 'events'
            $table->string('name', 100);                    // Display name
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 255)->nullable();
            
            // Categorization & Discovery
            $table->string('category', 50)->index();        // 'core', 'standard', 'premium'
            $table->json('tags')->nullable();               // ['giving', 'reporting']
            $table->integer('sort_order')->default(0);
            
            // Versioning & Compatibility
            $table->string('version', 20)->default('1.0.0');
            $table->string('min_platform_version', 20)->nullable();
            $table->string('max_platform_version', 20)->nullable();
            
            // Dependencies (JSON array of module keys)
            $table->json('dependencies')->nullable();
            $table->json('conflicts')->nullable();          // Modules that conflict
            
            // Pricing Configuration
            $table->boolean('is_free')->default(true)->index();
            $table->decimal('price_monthly', 10, 2)->nullable();
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->enum('billing_model', ['flat', 'per_user', 'usage_based'])->default('flat');
            
            // Feature Flags & Limits (within module)
            $table->json('features')->nullable();           // Granular feature flags
            $table->json('default_limits')->nullable();     // {'sms_per_month': 1000}
            
            // Marketplace Presentation
            $table->string('icon', 255)->nullable();
            $table->json('screenshots')->nullable();
            $table->string('documentation_url', 500)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->json('highlights')->nullable();         // ['✓ Unlimited funds', '✓ M-Pesa integration']
            
            // Installation Configuration
            $table->string('migration_path', 255)->nullable();
            $table->string('seeder_class', 255)->nullable();
            $table->json('install_hooks')->nullable();      // Pre/post install hooks
            $table->integer('estimated_install_time_seconds')->default(30);
            
            // Security & Approval
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_public')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_level', ['auto', 'admin', 'manual_review'])->default('auto');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('super_admins');
            $table->foreignId('updated_by')->nullable()->constrained('super_admins');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for Performance
            $table->index(['is_active', 'is_public', 'category'], 'idx_marketplace_browse');
            $table->index(['is_free', 'price_monthly'], 'idx_price_range');
            $table->fullText(['name', 'description', 'short_description'], 'idx_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
