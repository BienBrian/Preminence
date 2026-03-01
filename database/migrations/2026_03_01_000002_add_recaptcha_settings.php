<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'recaptcha_enabled')) {
                $table->boolean('recaptcha_enabled')->default(false)->after('contactus');
            }
            if (!Schema::hasColumn('settings', 'recaptcha_site_key')) {
                $table->string('recaptcha_site_key', 255)->nullable()->after('recaptcha_enabled');
            }
            if (!Schema::hasColumn('settings', 'recaptcha_secret_key')) {
                $table->string('recaptcha_secret_key', 255)->nullable()->after('recaptcha_site_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = ['recaptcha_enabled', 'recaptcha_site_key', 'recaptcha_secret_key'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
