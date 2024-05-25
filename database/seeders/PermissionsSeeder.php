<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Roles\AllPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = AllPermissions::$permissions;
        $permissions = [];
        foreach($groups as $key => $group){
            foreach($group as $permission){
                $permissions[] = Permission::create([
                    'name' => $permission,
                    'group' => $key,
                    'guard_name' => 'sanctum'
                ]);
            }
        }
        $role = Role::create(['name'=>User::ROLE_SUPPER_ADMIN]);
        $role->syncPermissions($permissions);
        $superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email'=>'admin@admin.com',
            'role'=> User::ROLE_SUPPER_ADMIN,
            'password' => Hash::make('123456')
        ]);
        $superAdmin->assignRole($role);
    }
}
