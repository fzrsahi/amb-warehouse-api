<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Models\Deposit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\VerifyDepositRequest;

class DepositController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $query = Deposit::select('id', 'deposit_at', 'created_by_user_id', 'status', 'company_id', 'nominal')
                ->with([
                    'company:id,name',
                    'createdBy:id,name'
                ]);

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id) {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat data deposit');
            }

            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data deposit berhasil diambil', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data deposit');
        }
    }

    public function store(StoreDepositRequest $request)
    {
        try {
            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return $this->notFoundResponse('Hanya Akun Mitra yang dapat membuat deposit');
            }

            DB::beginTransaction();

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('photos'), $photoName);
                $photoPath = 'photos/' . $photoName;
            }

            $deposit = Deposit::create([
                'deposit_at' => now(),
                'created_by_user_id' => $user->id,
                'nominal' => $request->nominal,
                'company_id' => $company->id,
                'photo' => $photoPath,
            ]);

            $deposit->remarks()->create([
                'user_id' => $user->id,
                'model' => 'App\Models\Deposit',
                'model_id' => $deposit->id,
                'status' => 'submit',
            ]);

            DB::commit();
            return $this->successResponse(null, 'Data deposit berhasil dibuat', code: 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat membuat data deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat data deposit');
        }
    }

    public function show(Deposit $deposit)
    {
        try {
            $user = request()->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id !== $deposit->company_id) {
                return $this->forbiddenResponse('Anda hanya dapat melihat deposit milik perusahaan Anda sendiri');
            }

            $deposit->load([
                'createdBy:id,name,email',
                'acceptedBy:id,name,email',
                'company:id,name,email,phone,address',
                'remarks' => function ($query) {
                    $query->with('user:id,name,email')
                        ->orderBy('created_at', 'desc');
                }
            ]);

            return $this->successResponse($deposit, 'Data deposit berhasil diambil', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data deposit');
        }
    }

    public function update(UpdateDepositRequest $request, Deposit $deposit)
    {
        try {
            $user = request()->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id !== $deposit->company_id) {
                return $this->forbiddenResponse('Anda hanya dapat mengubah deposit milik perusahaan Anda sendiri');
            }

            DB::beginTransaction();

            $photoPath = $deposit->photo;

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('photos'), $photoName);
                $photoPath = 'photos/' . $photoName;
            }

            $updateData = [];

            if ($request->has('nominal')) {
                $updateData['nominal'] = $request->nominal;
            }

            if ($photoPath !== $deposit->photo) {
                $updateData['photo'] = $photoPath;
            }

            if (!empty($updateData)) {
                $deposit->update($updateData);
            }

            DB::commit();
            return $this->successResponse(null, 'Data deposit berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memperbarui data deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui data deposit');
        }
    }

    public function verify(VerifyDepositRequest $request, Deposit $deposit)
    {
        try {
            DB::beginTransaction();
            $user = request()->user();

            $deposit->update([
                'status' => $request->status,
                'description' => $request->description,
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
            ]);

            $deposit->remarks()->create([
                'user_id' => $user->id,
                'model' => 'App\Models\Deposit',
                'model_id' => $deposit->id,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            DB::commit();

            $statusMessage = $request->status === 'approve' ? 'disetujui' : 'ditolak';
            return $this->successResponse(null, "Verifikasi deposit berhasil", code: 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memverifikasi deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memverifikasi deposit');
        }
    }
}
