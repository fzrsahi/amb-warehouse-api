<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;
use App\Traits\PaginationTrait;
use App\Http\Requests\PaginationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Traits\SearchFilterTrait;

class RoleController extends Controller
{
    use ApiResponse, PaginationTrait, SearchFilterTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $rolesQuery = Role::query();

            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            if ($user->hasRole('super-admin')) {
                $rolesQuery->whereIn('type', ['warehouse', 'company', 'super-admin']);
            } elseif ($userRoleType === 'warehouse') {
                $rolesQuery->where('type', 'warehouse');
            } elseif ($userRoleType === 'company') {
                $rolesQuery->where('type', 'company');
            } else {
                $rolesQuery->whereRaw('1 = 0');
            }

            // Get searchable fields for Role
            $searchableFields = $this->getSearchableFields('Role');

            // Apply search
            $this->applySearch($rolesQuery, $request, $searchableFields);

            // Apply sorting
            if ($request->sort_by) {
                $sortOrder = $request->sort_order ?? 'asc';
                $rolesQuery->orderBy($request->sort_by, $sortOrder);
            } else {
                $rolesQuery->orderBy('name', 'asc');
            }

            $rolesQuery->with(['permissions' => function ($query) {
                $query->select('id', 'name');
            }]);

            $result = $this->handlePaginationWithFormat($rolesQuery, $request, ["id", "name", "type"]);

            if (isset($result['data'])) {
                foreach ($result['data'] as $role) {
                    $role->permissions->makeHidden('pivot');
                }
            } else {
                foreach ($result as $role) {
                    $role->permissions->makeHidden('pivot');
                }
            }

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, message: 'Daftar peran berhasil diambil.', code: 200, pagination: $pagination);
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

            if ($user->hasRole('super-admin')) {
                $roleType = $request->type;
            } else {
                $roleType = $userRoleType;
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
                'type' => $roleType
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

            if ($role->name === 'super-admin') {
                DB::rollBack();
                return $this->errorResponse('Peran super-admin tidak dapat diubah.', 422);
            }

            $user = $request->user();
            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            if (!$user->hasRole('super-admin')) {
                if ($role->type !== $userRoleType) {
                    DB::rollBack();
                    return $this->forbiddenResponse('Anda tidak memiliki akses untuk mengubah peran dengan tipe ini.');
                }
            }

            if ($user->hasRole('super-admin')) {
                $roleType = $request->type;
            } else {
                $roleType = $userRoleType;
            }

            $role->update([
                'name' => $request->name,
                'type' => $roleType
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

            if ($role->name === 'super-admin') {
                return $this->errorResponse('Peran super-admin tidak dapat dihapus.', 422);
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
