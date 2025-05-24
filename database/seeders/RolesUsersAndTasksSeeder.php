<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Carbon;

class RolesUsersAndTasksSeeder extends Seeder
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

        // 2 admins
        $admins = [];
        for ($i = 1; $i <= 2; $i++) {
            $admin = User::firstOrCreate(
                ['email' => "admin$i@domain.com"],
                [
                    'name' => "Admin $i",
                    'password' => Hash::make('password'),
                ]
            );
            $admin->assignRole($adminRole);
            $admins[] = $admin;
        }

        // 20 regular users
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $user = User::firstOrCreate(
                ['email' => "user$i@domain.com"],
                [
                    'name' => "User $i",
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole($userRole);
            $users[] = $user;
        }

        $allUsers = array_merge($admins, $users);
        foreach ($allUsers as $user) {
            $taskCount = rand(3, 5);
            for ($j = 1; $j <= $taskCount; $j++) {
                Task::create([
                    'title' => "Task $j for {$user->name}",
                    'description' => "This is task $j assigned to {$user->name}.",
                    'user_id' => $user->id,
                    'deadline' => Carbon::now()->addDays(rand(3, 30)), 
                ]);
            }
        }
    }
}