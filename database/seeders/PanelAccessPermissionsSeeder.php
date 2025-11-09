<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PanelAccessPermissionsSeeder extends Seeder
{
    /**
     * Seed panel access permissions for admin and member panels.
     */
    public function run(): void
    {
        $guards = ['web'];
        $panels = ['admin', 'member'];
        
        foreach ($guards as $guard) {
            foreach ($panels as $panel) {
                $permissionName = "access {$panel}";
                
                // Create permission if it doesn't exist
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => $guard]
                );
                
                $this->command->info("✓ Permission '{$permissionName}' (guard: {$guard}) ensured.");
            }
            
            // Assign both panel permissions to Super Admin
            $superAdminRole = Role::where('name', 'Super Admin')
                ->where('guard_name', $guard)
                ->first();
            
            if ($superAdminRole) {
                $superAdminRole->givePermissionTo(['access admin', 'access member']);
                $this->command->info("✓ Super Admin role (guard: {$guard}) granted panel access permissions.");
            } else {
                $this->command->warn("⚠ Super Admin role not found for guard: {$guard}");
            }
        }
        
        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command->info('✓ Permission cache cleared.');
    }
}
