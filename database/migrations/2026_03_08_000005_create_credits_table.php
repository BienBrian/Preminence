<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('type', 20); // 'sms' or 'email'
            $table->integer('balance')->default(0);
            $table->integer('purchased')->default(0); // total credits purchased
            $table->integer('used')->default(0); // total credits used
            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'type']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // Insert default credits for tenant #1
        DB::table('credits')->insert([
            [
                'tenant_id' => 1,
                'type' => 'sms',
                'balance' => 1000,
                'purchased' => 1000,
                'used' => 0,
                'last_purchase_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tenant_id' => 1,
                'type' => 'email',
                'balance' => 500,
                'purchased' => 500,
                'used' => 0,
                'last_purchase_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
