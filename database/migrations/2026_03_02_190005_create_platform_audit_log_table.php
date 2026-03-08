<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();        // null = platform-level action
            $table->unsignedBigInteger('super_admin_id')->nullable();   // who did it (null if system)
            $table->unsignedBigInteger('user_id')->nullable();          // tenant user if impersonating
            $table->string('action', 100);    // "tenant.created", "tenant.suspended", "plan.changed", "module.toggled"
            $table->json('details')->nullable();  // before/after values, context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();  // no updated_at — audit log is immutable

            $table->index('tenant_id');
            $table->index('super_admin_id');
            $table->index(['tenant_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_audit_log');
    }
};
