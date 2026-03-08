<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Type of suspension: financial, terms_violation, admin_action, other
            $table->string('suspension_type', 50)->nullable()->after('status');
            // Brief reason for suspension
            $table->text('suspension_reason')->nullable()->after('suspension_type');
            // Detailed JSON data for suspension history
            $table->json('suspension_details')->nullable()->after('suspension_reason');
            // Amount due (for financial suspensions)
            $table->decimal('suspension_amount_due', 10, 2)->nullable()->after('suspension_details');
            // Currency for the amount
            $table->string('suspension_currency', 3)->default('KES')->after('suspension_amount_due');
            // When suspension ends (null = indefinite)
            $table->timestamp('suspension_ends_at')->nullable()->after('suspension_currency');
            // Who suspended the tenant
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspension_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'suspension_type',
                'suspension_reason',
                'suspension_details',
                'suspension_amount_due',
                'suspension_currency',
                'suspension_ends_at',
                'suspended_by',
            ]);
        });
    }
};
