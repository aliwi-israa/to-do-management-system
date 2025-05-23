<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@domain.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Regular User 1',
            'email' => 'user1@domain.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);

        User::create([
            'name' => 'Regular User 2',
            'email' => 'user2@domain.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);
        }
}
