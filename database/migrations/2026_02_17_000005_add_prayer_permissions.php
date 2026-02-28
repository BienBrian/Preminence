<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'View Prayer Requests',
            'Add Prayer Requests',
            'Edit Prayer Requests',
            'Manage Prayer Wall',
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->insertOrIgnore([
                'name'       => $perm,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'View Prayer Requests',
            'Add Prayer Requests',
            'Edit Prayer Requests',
            'Manage Prayer Wall',
        ])->delete();
    }
};
