<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user Super Admin dan langsung berikan role 'super-admin'
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole('super-admin');

        // Contoh membuat Petugas Gudang
        $warehouseStaff = User::create([
            'name' => 'Budi Gudang',
            'email' => 'warehousestaff@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $warehouseStaff->assignRole('warehouse-staff');
    }
}