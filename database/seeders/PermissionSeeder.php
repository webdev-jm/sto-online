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
            'Inventory Sales' => [
                'inventory sales access'   =>  'Allow account to access inventory sales list and details',
                'inventory sales add'      =>  'Allow account to add inventory sales',
                'inventory sales edit'     =>  'Allow account to edit inventory sales',
                'inventory sales delete'   =>  'Allow account to delete inventory sales',
            ],
            'User' => [
                'user access'   =>  'Allow account to access users list and details',
                'user add'      =>  'Allow account to add user',
                'user edit'     =>  'Allow account to edit user',
                'user delete'   =>  'Allow account to delete user',
            ],
            'Role' => [
                'role access'   =>  'Allow account to access roles list and details',
                'role add'      =>  'Allow account to add role',
                'role edit'     =>  'Allow account to edit role',
                'role delete'   =>  'Allow account to delete role',
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
