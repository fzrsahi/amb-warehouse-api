<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    use ApiResponse;

    public function store(StoreCompanyRequest $request)
    {
        try {
            Gate::authorize('create company');

            DB::beginTransaction();

            $company = Company::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'phone'   => $request->phone,
                'address' => $request->address,
                'logo'    => $request->logo,
            ]);
            DB::commit();

            return $this->successResponse([
                'company' => $company,
            ], 'Mitra baru berhasil dibuat');
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors(), 'Validasi gagal');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat mitra baru: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat mitra baru');
        }
    }
}
