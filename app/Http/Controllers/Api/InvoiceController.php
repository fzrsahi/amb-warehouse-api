<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Invoice;
use App\Http\Requests\PaginationRequest;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $query = Invoice::with(['company:id,name', 'createdBy:id,name']);

            $userRoleType = $user->roles->first()->type ?? null;

            if ($user->hasRole('super-admin') || $userRoleType === 'warehouse') {
            } elseif ($userRoleType === 'company') {
                $query->where('company_id', $user->company_id);
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk melihat invoice.');
            }

            $result = $this->handlePaginationWithFormat($query, $request);
            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Data invoice berhasil diambil', 200, $pagination);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data invoice: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil data invoice');
        }
    }
}
