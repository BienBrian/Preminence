<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->nullable()->index();
            $table->string('short_code', 20)->unique()->index();
            $table->text('original_url');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Table for detailed click analytics
        Schema::create('short_link_clicks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('short_link_id')->constrained('short_links')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamp('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_link_clicks');
        Schema::dropIfExists('short_links');
    }
};
