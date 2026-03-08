<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                    // "Free", "Starter", "Pro", "Enterprise"
            $table->string('slug', 50)->unique();           // "free", "starter", "pro", "enterprise"
            $table->decimal('price', 10, 2)->default(0);   // KES per billing cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->integer('max_users')->default(20);
            $table->integer('max_sms_per_month')->default(50);
            $table->integer('max_storage_mb')->default(100);
            $table->integer('trial_days')->default(14);

            // Which modules are included in this plan (used to auto-enable modules on signup)
            // e.g. {"people": true, "attendance": true, "finance": false, "mpesa": false}
            $table->json('modules')->nullable();

            // Legacy feature flags for simple boolean gating
            // e.g. {"api_access": true, "custom_domain": true, "priority_support": true}
            $table->json('features')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);     // display order on pricing page
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
