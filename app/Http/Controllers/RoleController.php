<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $rolesQuery = Role::query();

            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            if ($user->hasRole('super-admin')) {
                $rolesQuery->whereIn('name', ['super-admin', 'warehouse-admin', 'company-admin', 'warehouse-staff']);
            } elseif ($userRoleType === 'warehouse') {
                $rolesQuery->where('type', 'warehouse');
            } elseif ($userRoleType === 'company') {
                $rolesQuery->where('type', 'company');
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

    public function store(StoreRoleRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
                'type' => $userRoleType
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            $role->load(['permissions' => function ($query) {
                $query->select('id', 'name');
            }]);

            $role->permissions->makeHidden('pivot');

            return $this->successResponse(null, 'Peran berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat membuat peran: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat peran.');
        }
    }

    public function show(Role $role)
    {
        try {
            $role->load(['permissions' => function ($query) {
                $query->select('id', 'name');
            }]);

            $role->permissions->makeHidden('pivot');

            return $this->successResponse($role, 'Detail peran berhasil diambil.');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil detail peran: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil detail peran.');
        }
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            $role->update([
                'name' => $request->name,
                'type' => $userRoleType
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            $role->load(['permissions' => function ($query) {
                $query->select('id', 'name');
            }]);

            $role->permissions->makeHidden('pivot');

            return $this->successResponse(null, 'Peran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat memperbarui peran: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui peran.');
        }
    }

    public function destroy(Role $role)
    {
        try {
            if ($role->users()->exists()) {
                return $this->errorResponse('Peran tidak dapat dihapus karena masih digunakan oleh pengguna.', 422);
            }

            DB::beginTransaction();

            $role->syncPermissions([]);
            $role->delete();

            DB::commit();

            return $this->successResponse(null, 'Peran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat menghapus peran: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus peran.');
        }
    }
}
