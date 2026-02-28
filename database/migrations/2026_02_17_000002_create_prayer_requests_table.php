<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('category', 50)->nullable();
            $table->string('request_type', 20)->default('prayer');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('submitted_name', 100)->nullable();
            $table->string('submitted_phone', 20)->nullable();
            $table->string('submitted_email', 100)->nullable();
            $table->string('visibility', 20)->default('staff_only');
            $table->tinyInteger('is_anonymous')->default(0);
            $table->tinyInteger('is_urgent')->default(0);
            $table->string('status', 20)->default('new');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->integer('prayer_count')->default(0);
            $table->string('moderation_status', 20)->default('pending');
            $table->unsignedBigInteger('moderated_by')->nullable();
            $table->datetime('moderated_at')->nullable();
            $table->datetime('follow_up_at')->nullable();
            $table->datetime('answered_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->string('source', 20)->default('web');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('moderated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_requests');
    }
};
