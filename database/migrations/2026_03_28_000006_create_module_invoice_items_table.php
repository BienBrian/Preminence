<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_invoice_items', function (Blueprint $table) {
            $table->id();
            
            // Link to subscription and tenant
            $table->foreignId('subscription_id')
                  ->constrained('tenant_module_subscriptions')
                  ->cascadeOnDelete();
            $table->foreignId('tenant_id')
                  ->constrained('tenants')
                  ->cascadeOnDelete();
            
            // Invoice reference (can be linked to main invoices table later)
            $table->string('invoice_number', 100)->nullable();
            $table->foreignId('invoice_id')->nullable(); // For future main invoice integration
            
            // Item details
            $table->string('module_key', 50);
            $table->string('description');
            $table->enum('type', [
                'monthly_recurring',
                'yearly_recurring',
                'setup_fee',
                'prorated_charge',
                'prorated_credit',
                'overage',
                'refund',
                'adjustment',
            ]);
            
            // Pricing
            $table->decimal('unit_price', 12, 2);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('amount', 12, 2); // unit_price * quantity
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // amount + tax
            $table->string('currency', 3)->default('KES');
            
            // Billing period
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->integer('days_billed')->nullable(); // For prorated items
            
            // Status
            $table->enum('status', [
                'pending',      // Waiting to be invoiced
                'invoiced',     // Added to invoice
                'paid',         // Payment received
                'failed',       // Payment failed
                'refunded',     // Refunded
                'cancelled',    // Cancelled before invoicing
            ])->default('pending');
            
            // Payment tracking
            $table->timestamp('billed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable(); // 'mpesa', 'card', 'bank_transfer'
            $table->string('transaction_id', 255)->nullable();
            
            // Proration details
            $table->json('proration_details')->nullable(); // Store proration calculation details
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'status'], 'idx_invoice_items_lookup');
            $table->index(['subscription_id', 'status'], 'idx_subscription_items');
            $table->index(['invoice_number'], 'idx_invoice_number');
            $table->index(['type', 'status'], 'idx_type_status');
            $table->index(['period_start', 'period_end'], 'idx_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_invoice_items');
    }
};
