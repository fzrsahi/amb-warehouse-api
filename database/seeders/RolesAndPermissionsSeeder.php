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
        // Lebih baik definisikan semua kemungkinan izin di sini

        // Items
        Permission::create(['name' => 'view own items', 'guard_name' => 'web']); // Hanya lihat item milik perusahaannya
        Permission::create(['name' => 'view all items', 'guard_name' => 'web']); // Lihat semua item dari semua perusahaan
        Permission::create(['name' => 'create item', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit item', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete item', 'guard_name' => 'web']);

        // Invoices
        Permission::create(['name' => 'view own invoices', 'guard_name' => 'web']); // Hanya lihat invoice milik perusahaannya
        Permission::create(['name' => 'view all invoices', 'guard_name' => 'web']);
        Permission::create(['name' => 'create invoice', 'guard_name' => 'web']);

        // Deposits
        Permission::create(['name' => 'request deposit', 'guard_name' => 'web']); // Mitra bisa request deposit
        Permission::create(['name' => 'approve deposit', 'guard_name' => 'web']); // Admin internal yang menyetujui

        // Companies
        Permission::create(['name' => 'view own company', 'guard_name' => 'web']); // Melihat data perusahaannya sendiri
        Permission::create(['name' => 'view all companies', 'guard_name' => 'web']); // Melihat semua perusahaan
        Permission::create(['name' => 'create company', 'guard_name' => 'web']); // Membuat perusahaan
        Permission::create(['name' => 'edit company', 'guard_name' => 'web']); // Mengedit perusahaan

        // Users
        Permission::create(['name' => 'manage warehouse users', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage company users', 'guard_name' => 'web']);

        // Roles
        Permission::create(['name' => 'manage roles', 'guard_name' => 'web']);

        Permission::create(['name' => 'manage permissions', 'guard_name' => 'web']);

        // === BUAT PERAN (ROLES) & BERIKAN HAK AKSES ===

        // 1. Role: Warehouse Staff (Internal Operational Staff)
        $warehouseStaffRole = Role::create(['name' => 'warehouse-staff', 'guard_name' => 'web']);
        $warehouseStaffRole->givePermissionTo([
            'view all items', // Warehouse staff need to see all physical items
            'create item',
            'edit item',
        ]);

        // 2. Role: Partner Admin (Client Company Staff)
        $partnerAdminRole = Role::create(['name' => 'company-admin', 'guard_name' => 'web']);
        $partnerAdminRole->givePermissionTo([
            'view own company',
            'view own items',
            'view own invoices',
            'request deposit',
            'manage company users', // Can add other staff from their company
        ]);

        // 3. Role: Warehouse Admin (Internal Manager/Admin)
        $warehouseAdminRole = Role::create(['name' => 'warehouse-admin', 'guard_name' => 'web']);
        $warehouseAdminRole->givePermissionTo([
            'view all items',
            'delete item',
            'view all invoices',
            'create invoice',
            'approve deposit',
            'view all companies',
            'edit company',
            'manage warehouse users',
        ]);

        // 4. Role: Super Admin (Full Access)
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
