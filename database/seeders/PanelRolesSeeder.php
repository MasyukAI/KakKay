<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PanelRolesSeeder extends Seeder
{
    /**
     * Seed Admin and Member roles for panel access.
     */
    public function run(): void
    {
        $guards = ['web'];
        
        foreach ($guards as $guard) {
            // Create Admin role
            $adminRole = Role::firstOrCreate(
                ['name' => 'Admin', 'guard_name' => $guard]
            );
            
            // Admin has access to admin panel only
            $adminRole->givePermissionTo('access admin');
            $this->command->info("✓ Admin role (guard: {$guard}) created with admin panel access.");
            
            // Create Member role
            $memberRole = Role::firstOrCreate(
                ['name' => 'Member', 'guard_name' => $guard]
            );
            
            // Member has access to member panel only
            $memberRole->givePermissionTo('access member');
            $this->command->info("✓ Member role (guard: {$guard}) created with member panel access.");
        }
        
        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command->info('✓ Permission cache cleared.');
    }
}
