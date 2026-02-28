<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residences', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // Seed from existing distinct values in children.residence and scontacts.resident
        $values = collect();

        if (Schema::hasTable('children') && Schema::hasColumn('children', 'residence')) {
            $childResidences = DB::table('children')
                ->whereNotNull('residence')
                ->where('residence', '<>', '')
                ->distinct()
                ->pluck('residence');
            $values = $values->merge($childResidences);
        }

        if (Schema::hasTable('scontacts') && Schema::hasColumn('scontacts', 'resident')) {
            $scontactResidences = DB::table('scontacts')
                ->whereNotNull('resident')
                ->where('resident', '<>', '')
                ->distinct()
                ->pluck('resident');
            $values = $values->merge($scontactResidences);
        }

        $values->map(fn($v) => trim($v))->unique()->filter()->each(function ($name) {
            DB::table('residences')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residences');
    }
};
