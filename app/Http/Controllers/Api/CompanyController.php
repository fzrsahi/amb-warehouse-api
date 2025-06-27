<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Requests\PaginationRequest;
use App\Models\Company;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function store(StoreCompanyRequest $request)
    {
        try {
            DB::beginTransaction();

            $logoPath = null;
            if ($request->hasFile('company_logo')) {
                $logo = $request->file('company_logo');
                $logoName = time() . '.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('photos'), $logoName);
                $logoPath = 'photos/' . $logoName;
            }

            $company = Company::create([
                'name'    => $request->company_name,
                'email'   => $request->company_email,
                'phone'   => $request->company_phone,
                'address' => $request->company_address,
                'logo'    => $logoPath,
            ]);
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

    public function index(PaginationRequest $request)
    {
        try {
            $query = Company::query();
            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Mitra berhasil diambil', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data mitra: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data mitra');
        }
    }

    public function myCompany()
    {
        try {
            $user = request()->user();

            if (!$user->company_id) {
                return $this->notFoundResponse('Anda tidak terhubung dengan perusahaan manapun');
            }

            $company = Company::find($user->company_id);

            if (!$company) {
                return $this->notFoundResponse('Perusahaan tidak ditemukan');
            }

            return $this->successResponse($company, 'Data perusahaan berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data perusahaan: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data perusahaan');
        }
    }

    public function show(Company $company)
    {
        try {
            $user = request()->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id !== $company->id) {
                return $this->forbiddenResponse('Anda hanya dapat melihat perusahaan Anda sendiri');
            }

            return $this->successResponse($company, 'Data perusahaan berhasil diambil');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data perusahaan: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data perusahaan');
        }
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id !== $company->id) {
                return $this->forbiddenResponse('Anda hanya dapat mengedit perusahaan Anda sendiri');
            }

            $company->update([
                'name'    => $request->company_name,
                'email'   => $request->company_email,
                'phone'   => $request->company_phone,
                'address' => $request->company_address,
                'logo'    => $request->company_logo,
            ]);

            DB::commit();
            return $this->successResponse(null, 'Perusahaan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat memperbarui perusahaan: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui perusahaan');
        }
    }

    public function destroy(Company $company)
    {
        try {
            DB::beginTransaction();

            $user = request()->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk menghapus perusahaan');
            }

            if ($company->users()->exists()) {
                return $this->errorResponse('Perusahaan tidak dapat dihapus karena masih memiliki user yang terhubung', 422);
            }

            if ($company->deposits()->exists() || $company->items()->exists() || $company->invoices()->exists()) {
                return $this->errorResponse('Perusahaan tidak dapat dihapus karena masih memiliki data yang terhubung', 422);
            }

            $company->delete();

            DB::commit();
            return $this->successResponse(null, 'Perusahaan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus perusahaan: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus perusahaan');
        }
    }
}
