<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
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

        Role::firstOrCreate(['name' => 'superadmin'])->givePermissionTo(Permission::all());
        Role::firstOrCreate(['name' => 'api-users'])->givePermissionTo(Permission::all());
        Role::firstOrCreate(['name' => 'default_user']);
        Role::firstOrCreate(['name' => 'admin']);
    }
}
