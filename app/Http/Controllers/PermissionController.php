<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $permissionsQuery = Permission::query();

            if ($user->hasRole('super-admin')) {
                $permissionsQuery = Permission::query();
            } elseif ($user->hasRole('warehouse-admin')) {
                $permissionsQuery->where(function ($query) {
                    $query->where('name', 'like', '%item%')
                        ->orWhere('name', 'like', '%invoice%')
                        ->orWhere('name', 'like', '%deposit%')
                        ->orWhere('name', 'like', '%company%')
                        ->orWhere('name', 'like', '%user%')
                        ->orWhere('name', 'like', '%role%');
                });
            } elseif ($user->hasRole('company-admin')) {
                $permissionsQuery->where(function ($query) {
                    $query->where('name', 'like', '%own%')
                        ->orWhere('name', 'request deposit')
                        ->orWhere('name', 'create user');
                });
            } else {
                $permissionsQuery->whereRaw('1 = 0');
            }

            $result = $this->handlePaginationWithFormat($permissionsQuery, $request, ['id', 'name']);

            return $this->successResponse($result, 'Daftar izin berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar izin: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar izin.');
        }
    }
}
