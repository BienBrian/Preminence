<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tithe_message_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->text('message')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->index('tenant_id');
        });

        // Seed with default message based on the MPESA message format
        // This will be used for individual tithes added manually
        DB::table('tithe_message_settings')->insert([
            'tenant_id' => 1,
            'message' => "Dear {{NAME}}, Thank you for honouring the Lord with your finances (Proverbs 3:9). Your tithe of Ksh. {{AMOUNT}} through {{ACCOUNT}} has been received. Be blessed.\nGod loves a cheerful giver II Cor 9:7.\n#2026:Year of Growth.\nFor Prayers call Reverend Hosea (0721895977).",
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tithe_message_settings');
    }
};
