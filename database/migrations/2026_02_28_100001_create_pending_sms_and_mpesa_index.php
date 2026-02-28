<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table to queue SMS that failed due to no credits, retry later
        Schema::create('pending_sms', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->text('message');
            $table->string('transaction_id', 50)->nullable(); // MPESA TransID if applicable
            $table->string('category', 50)->default('mpesa');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('scheduled_at')->nullable(); // If set, don't retry before this time
            $table->timestamps();

            $table->index(['status', 'attempts']);
        });

        // Add index to mpesa_transactions for faster date-range queries on the logs page
        Schema::table('mpesa_transactions', function (Blueprint $table) {
            // Check if index exists before adding
            try {
                $table->index('created_at', 'mpesa_transactions_created_at_index');
            } catch (\Exception $e) {
                // Index may already exist
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_sms');

        Schema::table('mpesa_transactions', function (Blueprint $table) {
            try {
                $table->dropIndex('mpesa_transactions_created_at_index');
            } catch (\Exception $e) {}
        });
    }
};
