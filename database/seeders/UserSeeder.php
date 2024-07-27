<?php

namespace Database\Seeders;

use App\Http\Controllers\Shared\Helper;
use App\Models\Gender;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $table = 'users';
        $file = base_path("database/data/$table" . ".csv");
        $records = Helper::import_CSV($file);

        $role = Role::where('name', 'Super Admin')->first();
        if($role == null){
            $role = Role::updateOrCreate(['name' => 'User'], ['name' => 'User']);
            $role = Role::updateOrCreate(['name' => 'Super Admin'],['name' => 'Super Admin']);
            $role = Role::updateOrCreate(['name' => 'Member'],['name' => 'Member']);
            $role = Role::updateOrCreate(['name' => 'Pastor'],['name' => 'Pastor']);
        }

        foreach ($records as $key => $record) {
           $user = User::updateOrCreate(
                ['email' => $record['email']],  // columns to search for
                [   // data to update or insert
                    'firstname' => $record['firstname'],
                    'lastname'=> $record['lastname'],
                    'phone'=> $record['phone'],
                    'email'=> $record['email'],
                    'password'=> $record['password'],
                    'status'=> $record['status'],
                ]
            );
            $user->syncRoles([$role->name]);
        }
    }

}
