<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * List of MPESA reports permissions to create.
     */
    protected array $mpesaPermissions = [
        'View MPESA Reports' => 'Access the MPESA reports module',
        'View MPESA Transactions' => 'View the list of MPESA transactions',
        'Map MPESA References' => 'Map reference types to fund sources',
        'Manage MPESA Categories' => 'Create and manage summary categories',
        'Export MPESA Reports' => 'Print and export MPESA reports',
        'Rehash MPESA Transactions' => 'Re-check hash matching for transactions',
        'Auto-discover MPESA References' => 'Auto-discover new reference types',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permissions
        foreach ($this->mpesaPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description]
            );
        }

        // Assign all MPESA permissions to Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            foreach (array_keys($this->mpesaPermissions) as $permissionName) {
                $superAdmin->givePermissionTo($permissionName);
            }
        }

        // Assign basic permissions to Admin role if it exists
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = [
                'View MPESA Reports',
                'View MPESA Transactions',
                'Map MPESA References',
                'Export MPESA Reports',
            ];
            foreach ($adminPermissions as $permissionName) {
                $admin->givePermissionTo($permissionName);
            }
        }

        // Assign view-only permissions to Member role if it exists
        $member = Role::where('name', 'Member')->first();
        if ($member) {
            $member->givePermissionTo('View MPESA Reports');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_keys($this->mpesaPermissions) as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
