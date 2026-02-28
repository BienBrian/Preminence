<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('country_code', 10)->nullable()->after('address');
            $table->string('country_name', 100)->nullable()->after('country_code');
            $table->string('phone_code', 10)->nullable()->after('country_name');
            $table->string('county', 100)->nullable()->after('phone_code');
            $table->string('city', 100)->nullable()->after('county');
            $table->string('specific_location', 255)->nullable()->after('city');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'country_name', 'phone_code', 'county', 'city', 'specific_location']);
        });
    }
};
