<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table: saas_subscriptions
     *
     * Named with 'saas_' prefix because the existing app already has a
     * 'subscriptions' table (used for legacy subscription tracking).
     * This table tracks Pisti SaaS billing subscriptions per tenant.
     */
    public function up(): void
    {
        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('status', ['active', 'past_due', 'cancelled', 'trial', 'trial_expired'])->default('trial');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('payment_method', ['mpesa', 'card', 'bank', 'manual'])->nullable();
            $table->string('payment_reference')->nullable(); // Mpesa transaction ID
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            $table->string('invoice_number')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();             // superadmin manual notes
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_subscriptions');
    }
};
