<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the Resend SMS permission
        $permission = Permission::firstOrCreate([
            'name' => 'Resend SMS',
            'guard_name' => 'web',
        ]);

        // Assign to Admin role if it exists
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        // Also assign to any role that has "Edit Communication" permission
        $editCommPermission = Permission::where('name', 'Edit Communication')->first();
        if ($editCommPermission) {
            $rolesWithEditComm = $editCommPermission->roles;
            foreach ($rolesWithEditComm as $role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'Resend SMS')->where('guard_name', 'web')->delete();
    }
};
