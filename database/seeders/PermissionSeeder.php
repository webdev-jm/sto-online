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
            'Inventory' => [
                'inventory access'      => 'Allow user to access inventory list and details',
                'inventory create'      => 'Allow user to add inventory.',
                'inventory edit'        => 'Allow user to edit inventory.',
                'inventory delete'      => 'Allow user to delete inventory.',
                'inventory upload'      => 'Allow user to upload inventory data.',
            ],
            'Sales' => [
                'sales access'      => 'Allow user to access sales list and details',
                'sales create'      => 'Allow user to add sales.',
                'sales edit'        => 'Allow user to edit sales.',
                'sales delete'      => 'Allow user to delete sales.',
                'sales upload'      => 'Allow user to upload sales data.',
            ],
            'Customers' => [
                'customer access'   => 'Allow user to access customer list and details',
                'customer create'   => 'Allow user to add customer.',
                'customer edit'     => 'Allow user to edit customer.',
                'customer delete'   => 'Allow user to delete customer.',
                'customer upload'   => 'Allow user to upload customer data.',
            ],
            'Salesman' => [
                'salesman access'   => 'Allow user to access salesman list and details',
                'salesman create'   => 'Allow user to add salesman.',
                'salesman edit'     => 'Allow user to edit salesman.',
                'salesman delete'   => 'Allow user to delete salesman.',
                'salesman upload'   => 'Allow user to upload salesman data.',
            ],
            'Channel' => [
                'channel access'   => 'Allow user to access channel list and details',
                'channel create'   => 'Allow user to add channel.',
                'channel edit'     => 'Allow user to edit channel.',
                'channel delete'   => 'Allow user to delete channel.',
                'channel upload'   => 'Allow user to upload channel data.',
            ],
            'Area' => [
                'area access'   => 'Allow user to access areas list and details',
                'area create'   => 'Allow user to add area.',
                'area edit'     => 'Allow user to edit area.',
                'area delete'   => 'Allow user to delete area.',
            ],
            'Location' => [
                'location access'   => 'Allow user to access location list and details',
                'location create'   => 'Allow user to add location.',
                'location edit'     => 'Allow user to edit location.',
                'location delete'   => 'Allow user to delete location.',
                'location upload'   => 'Allow user to upload location data.',
            ],
            'Branch' => [
                'branch access'           =>  'Allow user to access branch list and details',
                'branch create'           =>  'Allow user to add branch',
                'branch edit'             =>  'Allow user to edit branch',
                'branch delete'           =>  'Allow user to delete branch',
            ],
            'Account Branch' => [
                'account branch access' =>  'Allow user to access account branch list and details',
                'account branch create' =>  'Allow user to add account branch',
                'account branch edit'   =>  'Allow user to edit account branch',
                'account branch delete' =>  'Allow user to delete account branch',
            ],
            'User' => [
                'user access'           =>  'Allow user to access users list and details',
                'user create'           =>  'Allow user to add user',
                'user edit'             =>  'Allow user to edit user',
                'user delete'           =>  'Allow user to delete user',
                'user assign account'   =>  'Allow user to assign accounts to user.',
                'user assign branch'    =>  'Allow user to assign branches to user',
            ],
            'Role' => [
                'role access'   =>  'Allow user to access roles list and details',
                'role create'   =>  'Allow user to add role',
                'role edit'     =>  'Allow user to edit role',
                'role delete'   =>  'Allow user to delete role',
            ],
            'Systemlog' => [
                'systemlog'   =>  'Allow user to access systemlog.',
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
