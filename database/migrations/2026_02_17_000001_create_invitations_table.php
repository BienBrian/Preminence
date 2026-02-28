<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 64)->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('via', ['sms', 'email']);
            $table->unsignedBigInteger('invited_by');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status', ['pending', 'started', 'completed', 'expired'])->default('pending');
            $table->tinyInteger('onboarding_step')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
