<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create twilios table if it doesn't exist
        if (!Schema::hasTable('twilios')) {
            Schema::create('twilios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->string('sid');
                $table->string('token');
                $table->string('number');
                $table->timestamps();
                
                $table->index('tenant_id', 'idx_twilios_tenant');
                $table->foreign('tenant_id', 'fk_twilios_tenant')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        } else {
            // Table exists but might be missing tenant_id or timestamps
            Schema::table('twilios', function (Blueprint $table) {
                if (!Schema::hasColumn('twilios', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')
                        ->default(1)
                        ->after('id');
                    $table->index('tenant_id', 'idx_twilios_tenant');
                    $table->foreign('tenant_id', 'fk_twilios_tenant')
                        ->references('id')
                        ->on('tenants')
                        ->onDelete('cascade');
                }
                
                if (!Schema::hasColumn('twilios', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        // Only drop if we created it
        if (Schema::hasTable('twilios')) {
            Schema::table('twilios', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_twilios_tenant');
                } catch (\Exception $e) {}
                try {
                    $table->dropIndex('idx_twilios_tenant');
                } catch (\Exception $e) {}
            });
            Schema::dropIfExists('twilios');
        }
    }
};
