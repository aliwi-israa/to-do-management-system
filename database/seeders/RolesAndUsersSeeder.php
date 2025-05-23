<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // permissions
        $permissions = [
            'manage users',
            'manage tasks',
            'view tasks',
            'edit tasks',
            'delete tasks',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());  // admin gets all permissions

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions(['view tasks', 'edit tasks']);

        // admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@domain.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // reglar user
        $user1 = User::firstOrCreate(
            ['email' => 'user1@domain.com'],
            [
                'name' => 'Regular User 1',
                'password' => Hash::make('password'),
            ]
        );
        $user1->assignRole($userRole);

        $user2 = User::firstOrCreate(
            ['email' => 'user2@domain.com'],
            [
                'name' => 'Regular User 2',
                'password' => Hash::make('password'),
            ]
        );
        $user2->assignRole($userRole);
    }
}
