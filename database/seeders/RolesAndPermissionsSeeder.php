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
        Permission::create(['name' => 'view own company', 'guard_name' => 'web']); // Mitra hanya bisa lihat data perusahaannya sendiri
        Permission::create(['name' => 'view all companies', 'guard_name' => 'web']);
        Permission::create(['name' => 'create company', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit company', 'guard_name' => 'web']);

        // Users
        Permission::create(['name' => 'manage warehouse users', 'guard_name' => 'web']);
        Permission::create(['name' => 'manage mitra users', 'guard_name' => 'web']);

        // === BUAT PERAN (ROLES) & BERIKAN HAK AKSES ===

        // 1. Role: Petugas Gudang (Staf Operasional Internal)
        $warehouseStaffRole = Role::create(['name' => 'petugas-gudang', 'guard_name' => 'web']);
        $warehouseStaffRole->givePermissionTo([
            'view all items', // Petugas gudang perlu melihat semua barang fisik yang ada
            'create item',
            'edit item',
        ]);

        // 2. Role: Admin Mitra (Staf dari Perusahaan Klien)
        $mitraAdminRole = Role::create(['name' => 'admin-mitra', 'guard_name' => 'web']);
        $mitraAdminRole->givePermissionTo([
            'view own company',
            'view own items',
            'view own invoices',
            'request deposit',
            'manage mitra users', // Dia bisa menambah staf lain dari perusahaannya
        ]);

        // 3. Role: Admin Warehouse (Manajer/Admin Internal)
        $warehouseAdminRole = Role::create(['name' => 'admin-warehouse', 'guard_name' => 'web']);
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

        // 4. Role: Super Admin (Akses Penuh)
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
