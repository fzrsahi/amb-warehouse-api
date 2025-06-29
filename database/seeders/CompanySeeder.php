<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $companies = [
            [
                'name' => 'PT. KOTAG PUTRA GORONTALO',
                'email' => 'kotagputra@gmail.com',
                'phone' => '081234567001',
                'address' => 'Jl. Gorontalo No. 1',
                'user_name' => 'Admin Kotag Putra',
                'user_email' => 'admin.kotagputra@gmail.com',
            ],
            [
                'name' => 'PT. PILOT AKBAR UTAMA',
                'email' => 'pilotakbar@gmail.com',
                'phone' => '081234567002',
                'address' => 'Jl. Akbar No. 2',
                'user_name' => 'Admin Pilot Akbar',
                'user_email' => 'admin.pilotakbar@gmail.com',
            ],
            [
                'name' => 'PT. METRO ADISYURI PERDANA',
                'email' => 'metroadisyuri@gmail.com',
                'phone' => '081234567003',
                'address' => 'Jl. Metro No. 3',
                'user_name' => 'Admin Metro Adisyuri',
                'user_email' => 'admin.metroadisyuri@gmail.com',
            ],
            [
                'name' => 'PT. SURYAGITA NUSARAYA',
                'email' => 'suryagita@gmail.com',
                'phone' => '081234567004',
                'address' => 'Jl. Suryagita No. 4',
                'user_name' => 'Admin Suryagita',
                'user_email' => 'admin.suryagita@gmail.com',
            ],
            [
                'name' => 'PT. IMKO JAYA PRADIPTA',
                'email' => 'imkojaya@gmail.com',
                'phone' => '081234567005',
                'address' => 'Jl. Imko No. 5',
                'user_name' => 'Admin Imko Jaya',
                'user_email' => 'admin.imkojaya@gmail.com',
            ],
            [
                'name' => 'PT. INTEGRASI AVIASI SOLUSI',
                'email' => 'integrasiaviasi@gmail.com',
                'phone' => '081234567006',
                'address' => 'Jl. Integrasi No. 6',
                'user_name' => 'Admin Integrasi Aviasi',
                'user_email' => 'admin.integrasiaviasi@gmail.com',
            ],
        ];

        foreach ($companies as $data) {
            DB::beginTransaction();
            try {
                $company = Company::create([
                    'name'    => $data['name'],
                    'email'   => $data['email'],
                    'phone'   => $data['phone'],
                    'address' => $data['address'],
                    'logo'    => null,
                ]);
                $user = User::create([
                    'name' => $data['user_name'],
                    'email' => $data['user_email'],
                    'password' => Hash::make('password'),
                    'company_id' => $company->id,
                ]);
                $user->assignRole('company-admin');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }
}
