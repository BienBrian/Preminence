<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "Happy Church Ruiru"
            $table->string('slug', 100)->unique();          // "happychurch-ruiru" (subdomain)
            $table->string('domain')->nullable()->unique(); // custom domain support
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial', 'trial_expired', 'cancelled'])->default('trial');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable(); // FK added after users table updated
            $table->boolean('setup_complete')->default(false); // onboarding wizard shown until true
            $table->integer('grace_period_days')->default(7);
            $table->json('settings')->nullable();           // reserved for tenant-level config overrides
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
