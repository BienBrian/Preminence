<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summary_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#007bff'); // hex color for UI
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active', 'sort_order'], 'idx_summary_cat_lookup');
            $table->foreign('tenant_id', 'fk_summary_cat_tenant')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summary_categories');
    }
};
