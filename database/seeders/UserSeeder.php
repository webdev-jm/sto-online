<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User([
            'name' => 'Administrator',
            'email' => 'admin@admin',
            'username' => 'admin',
            'password' => Hash::make('p4ssw0rd'),
            'status' => 0,
            'dark_mode' => 1
        ]);
        $user->save();

        // Assign superadmin role
        $user->assignRole('superadmin');

        $user = new User([
            'name' => 'API User',
            'email' => 'api@test',
            'username' => 'api-user',
            'password' => Hash::make('password'),
            'status' => 0,
            'dark_mode' => 1
        ]);
        $user->save();

        // Assign superadmin role
        $user->assignRole('api-users');
    }
}
