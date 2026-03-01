<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = ['View File Manager', 'Manage File Manager'];
        $adminRole = Role::where('name', 'Super Admin')->first();

        foreach ($permissions as $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            if ($adminRole) {
                $adminRole->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', ['View File Manager', 'Manage File Manager'])->delete();
    }
};
