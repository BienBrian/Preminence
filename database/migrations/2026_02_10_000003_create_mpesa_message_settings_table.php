<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_message_settings', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('updated_at')->nullable();
        });

        // Seed with current hardcoded message
        DB::table('mpesa_message_settings')->insert([
            'message' => "Dear, {{NAME}} Thank you for honouring the Lord with your finances (Proverbs 3:9). Your support of Ksh. {{AMOUNT}} through {{ACCOUNT}} account will support the ministry in great ways. Be blessed. \r\nGod loves a cheerful giver II Cor 9:7. \r\n#2026:Year of Growth. \r\n For Prayers call Reverend Hosea (0721895977).",
            'active' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_message_settings');
    }
};
