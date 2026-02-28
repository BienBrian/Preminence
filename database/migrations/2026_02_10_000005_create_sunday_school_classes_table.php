<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sunday_school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // Seed from existing distinct values in children table
        if (Schema::hasTable('children') && Schema::hasColumn('children', 'sunday_school_class')) {
            $classes = DB::table('children')
                ->whereNotNull('sunday_school_class')
                ->where('sunday_school_class', '<>', '')
                ->distinct()
                ->pluck('sunday_school_class');

            foreach ($classes as $class) {
                DB::table('sunday_school_classes')->insertOrIgnore([
                    'name' => trim($class),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sunday_school_classes');
    }
};
