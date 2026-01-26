<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Temporarily disable teams for role creation (global super_admin role)
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        // Create super_admin role if it doesn't exist (without team)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'saiffil@gmail.com'],
            [
                'name' => 'Saiffil Fariz',
                'password' => bcrypt('111111'),
            ]
        );

        // Ensure admin is verified and not a guest
        if (! $admin->email_verified_at) {
            $admin->email_verified_at = now();
            $admin->is_guest = false;
            $admin->save();
        }

        // Assign role (without team context for global admin)
        // syncWithoutDetaching prevents errors if already assigned
        $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);

        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed books and products
        $this->call([
            BookSeeder::class,
        ]);
    }
}
