<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('prayer_requests', function (Blueprint $table) {
            $table->string('submitted_alternate_phone')->nullable()->after('submitted_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('prayer_requests', function (Blueprint $table) {
            $table->dropColumn('submitted_alternate_phone');
        });
    }
};
