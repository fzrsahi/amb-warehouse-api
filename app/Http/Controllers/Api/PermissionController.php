<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Traits\SearchFilterTrait;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $permissionsQuery = Permission::query();

            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            if ($user->hasRole('super-admin')) {
                $permissionsQuery = Permission::query();
            } elseif ($userRoleType === 'warehouse') {
                $permissionsQuery->where(function ($query) {
                    $query->where('name', 'like', '%item%')
                        ->orWhere('name', 'like', '%invoice%')
                        ->orWhere('name', 'like', '%invoice_item%')
                        ->orWhere('name', 'like', '%deposit%')
                        ->orWhere('name', 'like', '%company%')
                        ->orWhere('name', 'like', '%user%')
                        ->orWhere('name', 'like', '%role%')
                        ->orWhere('name', 'like', '%permission%')
                        ->orWhere('name', 'like', '%airline%')
                        ->orWhere('name', 'like', '%location%')
                        ->orWhere('name', 'like', '%flight%')
                        ->orWhere('name', 'like', '%remark%');
                });
            } elseif ($userRoleType === 'company') {
                $permissionsQuery->where(function ($query) {
                    $query->where('name', 'like', '%company%')
                        ->orWhere('name', 'like', '%item%')
                        ->orWhere('name', 'like', '%invoice%')
                        ->orWhere('name', 'like', '%deposit%')
                        ->orWhere('name', 'like', '%remark%')
                        ->orWhere('name', 'like', '%user%')
                        ->orWhere('name', 'like', '%role%')
                        ->orWhere('name', 'like', '%permission%');
                });
            } else {
                $permissionsQuery->whereRaw('1 = 0');
            }

            // Get searchable fields for Permission
            $searchableFields = $this->getSearchableFields('Permission');

            // Apply search
            $this->applySearch($permissionsQuery, $request, $searchableFields);

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $permissionsQuery->orderBy($request->sort_by, $sortOrder);
            } else {
                $permissionsQuery->orderBy('name', 'asc');
            }

            $result = $this->handlePaginationWithFormat($permissionsQuery, $request, ['id', 'name']);

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, 'Daftar izin berhasil diambil.', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar izin: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar izin.');
        }
    }
}
