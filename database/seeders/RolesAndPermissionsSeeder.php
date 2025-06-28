<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === BUAT DAFTAR HAK AKSES (PERMISSIONS) ===
        // Permissions berdasarkan tabel-tabel yang ada di migrasi

        // Items
        Permission::create(['name' => 'view all item', 'guard_name' => 'web']);
        Permission::create(['name' => 'view own item', 'guard_name' => 'web']);
        Permission::create(['name' => 'show item', 'guard_name' => 'web']);
        Permission::create(['name' => 'create item', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit item', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete item', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage item', 'guard_name' => 'web']);
        Permission::create(['name' => 'verify item', 'guard_name' => 'web']);
        Permission::create(['name' => 'out item', 'guard_name' => 'web']);

        // Invoices
        Permission::create(['name' => 'view all invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'view own invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'show invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'create invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage invoice', 'guard_name' => 'web']);
        Permission::create(['name' => 'verify invoice', 'guard_name' => 'web']);

        // Invoice Items
        Permission::create(['name' => 'view all invoice_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'show invoice_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'create invoice_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit invoice_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete invoice_item', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage invoice_item', 'guard_name' => 'web']);
        // Companies
        Permission::create(['name' => 'view all company', 'guard_name' => 'web']);
        Permission::create(['name' => 'view own company', 'guard_name' => 'web']);
        Permission::create(['name' => 'show company', 'guard_name' => 'web']);
        Permission::create(['name' => 'create company', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit company', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete company', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage company', 'guard_name' => 'web']);

        // Users
        Permission::create(['name' => 'view all user', 'guard_name' => 'web']);
        Permission::create(['name' => 'show user', 'guard_name' => 'web']);
        Permission::create(['name' => 'create user', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit user', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete user', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage user', 'guard_name' => 'web']);
        // Roles
        Permission::create(['name' => 'view all role', 'guard_name' => 'web']);
        Permission::create(['name' => 'show role', 'guard_name' => 'web']);
        Permission::create(['name' => 'create role', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit role', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete role', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage role', 'guard_name' => 'web']);
        // Permissions
        Permission::create(['name' => 'view all permission', 'guard_name' => 'web']);

        // Airlines
        Permission::create(['name' => 'view all airline', 'guard_name' => 'web']);
        Permission::create(['name' => 'show airline', 'guard_name' => 'web']);
        Permission::create(['name' => 'create airline', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit airline', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete airline', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage airline', 'guard_name' => 'web']);
        // Locations
        Permission::create(['name' => 'view all location', 'guard_name' => 'web']);
        Permission::create(['name' => 'show location', 'guard_name' => 'web']);
        Permission::create(['name' => 'create location', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit location', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete location', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage location', 'guard_name' => 'web']);
        // Flights
        Permission::create(['name' => 'view all flight', 'guard_name' => 'web']);
        Permission::create(['name' => 'show flight', 'guard_name' => 'web']);
        Permission::create(['name' => 'create flight', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit flight', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete flight', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage flight', 'guard_name' => 'web']);
        // Deposits
        Permission::create(['name' => 'view all deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'show deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'create deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage deposit', 'guard_name' => 'web']);
        Permission::create(['name' => 'verify deposit', 'guard_name' => 'web']);
        // Remarks
        Permission::create(['name' => 'view all remark', 'guard_name' => 'web']);
        Permission::create(['name' => 'show remark', 'guard_name' => 'web']);
        Permission::create(['name' => 'create remark', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit remark', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete remark', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage remark', 'guard_name' => 'web']);
        // === BUAT PERAN (ROLES) & BERIKAN HAK AKSES ===

        // 1. Role: Warehouse Staff (Internal Operational Staff)
        $warehouseStaffRole = Role::create(['name' => 'warehouse-staff', 'guard_name' => 'web', 'type' => 'warehouse']);
        $warehouseStaffRole->givePermissionTo([
            'view all item',
            'show item',
            'create item',
            'edit item',
            'out item',
            'view all flight',
            'show flight',
            'view all location',
            'show location',
        ]);

        // 2. Role: Partner Admin (Client Company Staff)
        $partnerAdminRole = Role::create(['name' => 'company-admin', 'guard_name' => 'web', 'type' => 'company']);
        $partnerAdminRole->givePermissionTo([
            'view all company',
            'view own company',
            'show company',
            'view all item',
            'view own item',
            'show item',
            'view all invoice',
            'view own invoice',
            'show invoice',
            'verify invoice',
            'view all deposit',
            'show deposit',
            'create deposit',
            'view all remark',
            'show remark',
            'create remark',
            'view all user',
            'show user',
            'create user',
            'edit user',
            'view all role',
            'show role',
            'edit role',
            'delete role',
            'create role',
            'view all permission',
            'edit deposit',
            'delete deposit',
            'manage deposit',
            'create item',
            'edit item',
            'delete item',
            'view all invoice',
            'view own invoice',
            'show invoice',
        ]);

        // 3. Role: Warehouse Admin (Internal Manager/Admin)
        $warehouseAdminRole = Role::create(['name' => 'warehouse-admin', 'guard_name' => 'web', 'type' => 'warehouse']);
        $warehouseAdminRole->givePermissionTo([
            'view all item',
            'view own item',
            'show item',
            'create item',
            'edit item',
            'delete item',
            'out item',
            'view all invoice',
            'view own invoice',
            'show invoice',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'view all invoice_item',
            'show invoice_item',
            'create invoice_item',
            'edit invoice_item',
            'delete invoice_item',
            'view all deposit',
            'show deposit',
            'create deposit',
            'edit deposit',
            'delete deposit',
            'view all company',
            'show company',
            'edit company',
            'delete company',
            'view all user',
            'show user',
            'create user',
            'edit user',
            'delete user',
            'view all role',
            'show role',
            'view all permission',
            'view all airline',
            'show airline',
            'create airline',
            'edit airline',
            'delete airline',
            'view all location',
            'show location',
            'create location',
            'edit location',
            'delete location',
            'view all flight',
            'show flight',
            'create flight',
            'edit flight',
            'delete flight',
            'view all remark',
            'show remark',
            'create remark',
            'edit remark',
            'delete remark',
            'verify deposit',
            'verify item',
        ]);

        // 4. Role: Super Admin (Full Access)
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web', 'type' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::where('name', '!=', 'view own company')->get());
    }
}
