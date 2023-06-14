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
            'Sales' => [
                'sales access'      => 'Allow user to access customer list and details',
                'sales upload'      => 'Allow user to upload sales data.',
                'sales add'         => 'Allow user to add customer.',
                'sales edit'        => 'Allow user to edit customer.',
                'sales delete'      => 'Allow user to delete customer.',
            ],
            'Customers' => [
                'customer access'   => 'Allow user to access customer list and details',
                'customer add'      => 'Allow user to add customer.',
                'customer edit'     => 'Allow user to edit customer.',
                'customer delete'   => 'Allow user to delete customer.',
            ],
            'Salesman' => [
                'salesman access'   => 'Allow user to access salesman list and details',
                'salesman add'      => 'Allow user to add salesman.',
                'salesman edit'     => 'Allow user to edit salesman.',
                'salesman delete'   => 'Allow user to delete salesman.',
            ],
            'Channel' => [
                'channel access'   => 'Allow user to access channel list and details',
                'channel add'      => 'Allow user to add channel.',
                'channel edit'     => 'Allow user to edit channel.',
                'channel delete'   => 'Allow user to delete channel.',
            ],
            'Area' => [
                'area access'   => 'Allow user to access areas list and details',
                'area add'      => 'Allow user to add area.',
                'area edit'     => 'Allow user to edit area.',
                'area delete'   => 'Allow user to delete area.',
            ],
            'Location' => [
                'location access'   => 'Allow user to access location list and details',
                'location add'      => 'Allow user to add location.',
                'location edit'     => 'Allow user to edit location.',
                'location delete'   => 'Allow user to delete location.',
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
