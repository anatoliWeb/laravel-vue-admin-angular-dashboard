<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Seed full RBAC demo data.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Permissions
         */
        $permissions = [
            'access_admin',
            'users.create',
            'users.edit',
            'users.delete',
            'users.view',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'tokens.view',
            'tokens.create',
            'tokens.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm],
                ['description' => ucfirst(str_replace('.', ' ', $perm))]
            );
        }

        // WHY:
        // Permissions use entity.action format for consistency
        // and easier scaling across modules.
        Permission::where('name', 'like', 'admin.%')->delete();

        /**
         * Roles
         */
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $managerRole = Role::updateOrCreate(
            ['name' => 'manager'],
            ['description' => 'Manager']
        );

        $userRole = Role::updateOrCreate(
            ['name' => 'user'],
            ['description' => 'User']
        );

        /**
         * Assign permissions to roles
         */
        // WHY:
        // Admin must receive full permission set so newly added capabilities
        // (including roles.* actions) are immediately available in UI and API.
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Manager: can view and edit users.
        $managerRole->permissions()->sync(
            Permission::whereIn('name', [
                'users.view',
                'users.edit',
            ])->pluck('id')
        );

        // User: read-only access to users.
        $userRole->permissions()->sync(
            Permission::whereIn('name', [
                'users.view',
            ])->pluck('id')
        );

        /**
         * Admin user
         */
        $admin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->roles()->sync([$adminRole->id]);

        /**
         * Create multiple users
         */
        for ($i = 1; $i <= 15; $i++) {

            $user = User::updateOrCreate(
                ['email' => "user{$i}@test.com"],
                [
                    'name' => "User {$i}",
                    'password' => Hash::make('password'),
                ]
            );

            /**
             * Random role assignment
             */
            $role = match (true) {
                $i <= 2 => $adminRole,
                $i <= 6 => $managerRole,
                default => $userRole,
            };

            $user->roles()->sync([$role->id]);

            /**
             * Direct permissions (occasionally)
             */
            if ($i % 3 === 0) {
                $user->permissions()->syncWithoutDetaching([
                    Permission::where('name', 'users.view')->first()->id
                ]);
            }

            /**
             * Create tokens
             */
            for ($t = 1; $t <= rand(1, 3); $t++) {
                $user->createToken("token_{$i}_{$t}");
            }
        }
    }
}
