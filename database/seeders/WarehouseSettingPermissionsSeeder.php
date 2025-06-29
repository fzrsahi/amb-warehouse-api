<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class WarehouseSettingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create warehouse setting permissions if they don't exist
        $permissions = [
            'view all warehouse_setting',
            'show warehouse_setting',
            'create warehouse_setting',
            'edit warehouse_setting',
            'delete warehouse_setting',
            'manage warehouse_setting',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to existing roles
        $warehouseStaffRole = Role::where('name', 'warehouse-staff')->first();
        if ($warehouseStaffRole) {
            $warehouseStaffRole->givePermissionTo([
                'view all warehouse_setting',
                'show warehouse_setting',
            ]);
        }

        $warehouseAdminRole = Role::where('name', 'warehouse-admin')->first();
        if ($warehouseAdminRole) {
            $warehouseAdminRole->givePermissionTo([
                'view all warehouse_setting',
                'show warehouse_setting',
                'create warehouse_setting',
                'edit warehouse_setting',
                'delete warehouse_setting',
                'manage warehouse_setting',
            ]);
        }

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }
    }
}
