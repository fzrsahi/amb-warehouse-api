<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $rolesQuery = Role::query();

            if ($user->hasRole('super-admin')) {
                $rolesQuery->whereIn('name', ['super-admin', 'warehouse-admin', 'company-admin', 'warehouse-staff']);
            } elseif ($user->hasRole('warehouse-admin')) {
                $rolesQuery->whereIn('name', ['warehouse-admin', 'warehouse-staff']);
            } elseif ($user->hasRole('company-admin')) {
                $rolesQuery->whereIn('name', ['company-admin']);
            } else {
                $rolesQuery->whereRaw('1 = 0');
            }

            $roles = $rolesQuery->with(['permissions' => function ($query) {
                $query->select('id', 'name');
            }])->get(["id", "name"]);

            $roles->each(function ($role) {
                $role->permissions->makeHidden('pivot');
            });

            return $this->successResponse($roles, 'Daftar peran berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar peran: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar peran.');
        }
    }
}
