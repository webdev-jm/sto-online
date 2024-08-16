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
                'inventory restore'     => 'Allow user to restore inventory.',
            ],
            'Sales' => [
                'sales access'      => 'Allow user to access sales list and details',
                'sales create'      => 'Allow user to add sales.',
                'sales edit'        => 'Allow user to edit sales.',
                'sales delete'      => 'Allow user to delete sales.',
                'sales upload'      => 'Allow user to upload sales data.',
                'sales restore'     => 'Allow user to restore sales.'
            ],
            'Customers' => [
                'customer access'               => 'Allow user to access customer list and details',
                'customer create'               => 'Allow user to add customer.',
                'customer edit'                 => 'Allow user to edit customer.',
                'customer delete'               => 'Allow user to delete customer.',
                'customer upload'               => 'Allow user to upload customer data.',
                'customer restore'              => 'Allow user to restore customer data.',
                'customer parked'               => 'Allow user to view parked customers',
                'customer parked validation'    => 'Allow user to validate parked customers.',
            ],
            'Salesman' => [
                'salesman access'   => 'Allow user to access salesman list and details',
                'salesman create'   => 'Allow user to add salesman.',
                'salesman edit'     => 'Allow user to edit salesman.',
                'salesman delete'   => 'Allow user to delete salesman.',
                'salesman upload'   => 'Allow user to upload salesman data.',
                'salesman restore'  => 'Allow user to restore salesman data.',
            ],
            'District' => [
                'district access'   => 'Allow user to access ditrict list and details',
                'district create'   => 'Allow user to add district',
                'district edit'     => 'Allow user to edit district',
                'district delete'   => 'Allow user to delete district',
                'district restore'  => 'Allow user to restore district data',
            ],
            'Channel' => [
                'channel access'   => 'Allow user to access channel list and details',
                'channel create'   => 'Allow user to add channel.',
                'channel edit'     => 'Allow user to edit channel.',
                'channel delete'   => 'Allow user to delete channel.',
                'channel upload'   => 'Allow user to upload channel data.',
                'channel restore'  => 'Allow user to restore channel data.',
            ],
            'Area' => [
                'area access'   => 'Allow user to access areas list and details',
                'area create'   => 'Allow user to add area.',
                'area edit'     => 'Allow user to edit area.',
                'area delete'   => 'Allow user to delete area.',
                'area restore'  => 'Allow user to restore area data.',
            ],
            'Location' => [
                'location access'   => 'Allow user to access location list and details',
                'location create'   => 'Allow user to add location.',
                'location edit'     => 'Allow user to edit location.',
                'location delete'   => 'Allow user to delete location.',
                'location upload'   => 'Allow user to upload location data.',
                'location restore'  => 'Allow user to restore location data.',
            ],
            'Account' => [
                'account access'    => 'Allow user to access account list and details',
                'account create'    => 'Allow user to add account',
                'account edit'      => 'Allow user to edit account',
                'account delete'    => 'Allow user to delete account',
                'account restore'   => 'Allow user to restore account',
            ],
            'Account Branch' => [
                'account branch access'         =>  'Allow user to access account branch list and details',
                'account branch create'         =>  'Allow user to add account branch',
                'account branch edit'           =>  'Allow user to edit account branch',
                'account branch delete'         =>  'Allow user to delete account branch',
                'account branch restore'        =>  'Allow user to restore account branch.',
                'account branch generate token' =>  'Allow user to generate account branch token.',
            ],
            'Customer Ubo Job' => [
                'customer ubo access'   => 'Allow user to access customer ubo jobs',
                'customer ubo job'      => 'Allow user to run customer ubo job',
            ],
            'User' => [
                'user access'           =>  'Allow user to access users list and details',
                'user create'           =>  'Allow user to add user',
                'user edit'             =>  'Allow user to edit user',
                'user delete'           =>  'Allow user to delete user',
                'user assign account'   =>  'Allow user to assign accounts to user.',
                'user assign branch'    =>  'Allow user to assign branches to user',
                'user change signature' =>  'Allow user to change signature in profile settings.',
                'user restore'          => 'Allow user to restore user',
            ],
            'Role' => [
                'role access'   =>  'Allow user to access roles list and details',
                'role create'   =>  'Allow user to add role',
                'role edit'     =>  'Allow user to edit role',
                'role delete'   =>  'Allow user to delete role',
            ],
            'Systemlog' => [
                'systemlog'   =>  'Allow user to access systemlog.',
                'error logs'  =>  'Allow user to view error logs.'
            ],
            'Report' => [
                'report access' => 'Allow user to access reports',
                'report vmi'    => 'Allow user to access vmi reports',
                'report sto'    => 'Allow user to access sto reports.'
            ],
            'Template' => [
                'template access' => 'Allow user to access templates',
                'template create' => 'Allow user to create templates',
                'template edit'   => 'Allow user to edit templates',
                'template delete' => 'Allow user to delete templates',
                'template restore' => 'Allow user to restore templates',
            ],
            'Purchase Order' => [
                'purchase order access' => 'Allow user to access purchase orders',
                'purchase order upload' => 'Allow user to upload purchase orders',
            ],
            'Stock On Hand' => [
                'stock on hand access' => 'Allow user to access stock on hand',
                'stock on hand upload' => 'Allow user to upload stock on hand',
            ],
            'Stock Transfer' => [
                'stock transfer access' => 'Allow user to access stock transfer',
                'stock transfer upload' => 'Allow user to upload stock transfer',
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
