<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
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

            $permissions = $permissionsQuery->get(['id', 'name']);

            return $this->successResponse($permissions, 'Daftar izin berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar izin: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar izin.');
        }
    }
}
