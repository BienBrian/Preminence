<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_settings', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->boolean('active')->default(true);
            $table->string('send_time', 10)->default('08:00');
            $table->timestamp('updated_at')->nullable();
        });

        // Seed default birthday message
        DB::table('birthday_settings')->insert([
            'message' => 'Happy Birthday {{NAME}}! Wishing you a blessed year ahead. - {{CHURCH}}',
            'active' => true,
            'send_time' => '08:00',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_settings');
    }
};
