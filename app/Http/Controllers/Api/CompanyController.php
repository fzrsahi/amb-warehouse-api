<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    use ApiResponse;

    public function store(StoreCompanyRequest $request)
    {
        try {
            DB::beginTransaction();

            $company = Company::create([
                'name'    => $request->company_name,
                'email'   => $request->company_email,
                'phone'   => $request->company_phone,
                'address' => $request->company_address,
                'logo'    => $request->company_logo,
            ]);

            Log::info('Company created: ' . $company->id);

            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($request->user_password),
                'company_id' => $company->id,
            ]);

            $user->assignRole('company-admin');
            DB::commit();
            return $this->successResponse(null, 'Mitra baru berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat mitra baru: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat mitra baru');
        }
    }


    public function index()
    {
        $companies = Company::all();
        return $this->successResponse($companies, 'Mitra berhasil diambil');
    }
}
