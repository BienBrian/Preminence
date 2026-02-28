<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('color', 20)->default('primary');
            $table->timestamps();
        });

        Schema::create('prayer_request_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('prayer_request_id');
            $table->unsignedBigInteger('prayer_tag_id');

            $table->foreign('prayer_request_id')->references('id')->on('prayer_requests')->cascadeOnDelete();
            $table->foreign('prayer_tag_id')->references('id')->on('prayer_tags')->cascadeOnDelete();

            $table->primary(['prayer_request_id', 'prayer_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_request_tag');
        Schema::dropIfExists('prayer_tags');
    }
};
