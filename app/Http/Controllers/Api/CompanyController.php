<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    use ApiResponse;

    public function store(StoreCompanyRequest $request)
    {
        try {
            DB::beginTransaction();

            $company = Company::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'phone'   => $request->phone,
                'address' => $request->address,
                'logo'    => $request->logo,
            ]);
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
