<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix duplicate phones before adding unique constraint
        $duplicates = DB::table('users')
            ->select('phone', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->groupBy('phone')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table('users')->where('phone', $dup->phone)->orderBy('id')->get();
            foreach ($rows->skip(1) as $i => $row) {
                DB::table('users')->where('id', $row->id)->update([
                    'phone' => $row->phone . '0' . ($i + 1),
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            // Make email nullable (drop existing unique, re-add as nullable-friendly)
            $table->string('email')->nullable()->change();

            // Add unique index on phone
            $table->unique('phone', 'users_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
            $table->string('email')->nullable(false)->change();
        });
    }
};
