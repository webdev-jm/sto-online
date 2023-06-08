<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions_arr = [
            'Area' => [
                'area access'   => 'Allow user to access areas list and details',
                'area add'      => 'Allow user to add area.',
                'area edit'      => 'Allow user to edit area.',
                'area delete'      => 'Allow user to delete area.',
            ],
            'User' => [
                'user access'           =>  'Allow user to access users list and details',
                'user add'              =>  'Allow user to add user',
                'user edit'             =>  'Allow user to edit user',
                'user delete'           =>  'Allow user to delete user',
                'user assign account'   =>  'Allow user to assign accounts to user.'
            ],
            'Role' => [
                'role access'   =>  'Allow user to access roles list and details',
                'role add'      =>  'Allow user to add role',
                'role edit'     =>  'Allow user to edit role',
                'role delete'   =>  'Allow user to delete role',
            ],
        ];

        foreach($permissions_arr as $module => $permissions) {
            foreach($permissions as $permission => $description) {
                Permission::create([
                    'name' => $permission,
                    'module' => $module,
                    'description' => $description
                ]);
            }
        }

    }
}
