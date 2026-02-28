<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_request_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prayer_request_id');
            $table->unsignedBigInteger('user_id');
            $table->text('note');
            $table->string('type', 20)->default('note');
            $table->timestamps();

            $table->foreign('prayer_request_id')->references('id')->on('prayer_requests')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_request_notes');
    }
};
