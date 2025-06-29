<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositIndexRequest;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use App\Models\Deposit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\VerifyDepositRequest;

class DepositController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(DepositIndexRequest $request)
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

            // Get searchable fields for Deposit
            $searchableFields = $this->getSearchableFields('Deposit');

            // Apply search
            $this->applySearch($query, $request, $searchableFields);

            // Apply status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $result = $this->handlePaginationWithFormat($query, $request);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            // Tambahkan informasi saldo jika user adalah company
            if ($user->company_id) {
                $company = $user->company;
                $balanceInfo = [
                    'total_deposit' => $company->getTotalDepositBalance(),
                    'total_payments' => $company->getTotalPayments(),
                    'remaining_balance' => $company->getRemainingBalance(),
                ];

                $responseData = [
                    'deposits' => $data,
                    'balance_info' => $balanceInfo,
                ];
            } else {
                $responseData = $data;
            }

            return $this->successResponse($responseData, 'Data deposit berhasil diambil', code: 200, pagination: $pagination);
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
                    $query->where('model', 'App\Models\Deposit')
                        ->with('user:id,name,email')
                        ->orderBy('created_at', 'desc');
                }
            ]);

            $responseData = $deposit->toArray();

            // Tambahkan informasi saldo jika user adalah company
            if ($user->company_id && $user->company_id === $deposit->company_id) {
                $company = $user->company;
                $responseData['balance_info'] = [
                    'total_deposit' => $company->getTotalDepositBalance(),
                    'total_payments' => $company->getTotalPayments(),
                    'remaining_balance' => $company->getRemainingBalance(),
                ];
            }

            return $this->successResponse($responseData, 'Data deposit berhasil diambil', code: 200);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil data deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data deposit');
        }
    }

    public function update(UpdateDepositRequest $request, Deposit $deposit)
    {
        try {
            DB::beginTransaction();
            $user = request()->user();

            if ($user->hasRole('super-admin')) {
            } elseif ($user->hasRole('warehouse-admin')) {
            } elseif ($user->company_id !== $deposit->company_id) {
                return $this->forbiddenResponse('Anda hanya dapat mengubah deposit milik perusahaan Anda sendiri');
            }

            if ($deposit->status === 'approve') {
                return $this->errorResponse('Deposit yang sudah disetujui tidak dapat diedit', 403);
            }

            $photoPath = $deposit->photo;

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('photos'), $photoName);
                $photoPath = 'photos/' . $photoName;
            }

            $updateData = [];

            // Get all input data from request instead of just validated data
            $allInput = $request->all();

            if (isset($allInput['nominal']) && !is_null($allInput['nominal']) && $allInput['nominal'] !== '') {
                $updateData['nominal'] = $allInput['nominal'];
            }

            if ($photoPath !== $deposit->photo) {
                $updateData['photo'] = $photoPath;
            }

            if (!empty($updateData)) {
                $updateData['status'] = 'submit';
                $deposit->update($updateData);

                $deposit->remarks()->create([
                    'user_id' => $user->id,
                    'model' => 'App\Models\Deposit',
                    'model_id' => $deposit->id,
                    'status' => 'submit',
                ]);
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

            // Tambahkan informasi saldo jika deposit disetujui
            $responseData = null;
            if ($request->status === 'approve') {
                $company = $deposit->company;
                $responseData = [
                    'balance_info' => [
                        'total_deposit' => $company->getTotalDepositBalance(),
                        'total_payments' => $company->getTotalPayments(),
                        'remaining_balance' => $company->getRemainingBalance(),
                    ]
                ];
            }

            return $this->successResponse($responseData, "Verifikasi deposit berhasil", code: 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memverifikasi deposit: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memverifikasi deposit');
        }
    }
}
