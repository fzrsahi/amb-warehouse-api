<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\PaginationTrait;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ApiResponse, PaginationTrait;

    public function index(PaginationRequest $request)
    {
        try {
            $user = $request->user();
            $usersQuery = User::query();

            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            if ($user->hasRole('super-admin')) {
                $usersQuery->with(['roles:id,name,type', 'company:id,name']);
            } elseif ($userRoleType === 'warehouse') {
                $usersQuery->whereHas('roles', function ($query) {
                    $query->where('type', 'warehouse');
                })->with(['roles:id,name,type', 'company:id,name']);
            } elseif ($userRoleType === 'company') {
                $usersQuery->whereHas('roles', function ($query) {
                    $query->where('type', 'company');
                })->where('company_id', $user->company_id)
                    ->with(['roles:id,name,type', 'company:id,name']);
            } else {
                $usersQuery->whereRaw('1 = 0');
            }

            $result = $this->handlePaginationWithFormat($usersQuery, $request, ["id", "name", "email", "company_id"]);

            if (isset($result['data'])) {
                foreach ($result['data'] as $userData) {
                    if ($userData->roles) {
                        $userData->roles->makeHidden('pivot');
                    }
                }
            } else {
                foreach ($result as $userData) {
                    if ($userData->roles) {
                        $userData->roles->makeHidden('pivot');
                    }
                }
            }

            $pagination = $result["pagination"] ?? null;
            $data = $result["data"] ?? $result;

            return $this->successResponse($data, message: 'Daftar user berhasil diambil.', code: 200, pagination: $pagination);
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengambil daftar user: ' . $e->getMessage());
            return $this->serverErrorResponse('Terjadi kesalahan saat mengambil daftar user.');
        }
    }

    public function store(UserStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $userRole = $user->roles->first();
            $userRoleType = $userRole ? $userRole->type : null;

            $validationErrors = [];

            if ($user->hasRole('super-admin')) {
                $companyId = $request->company_id;
            } elseif ($userRoleType === 'warehouse') {
                if ($request->filled('company_id')) {
                    $validationErrors['company_id'] = ['Warehouse tidak perlu memasukkan company_id'];
                }
                $companyId = null;
            } elseif ($userRoleType === 'company') {
                if (!$request->filled('company_id')) {
                    $validationErrors['company_id'] = ['Company wajib memasukkan company_id'];
                } elseif ($request->company_id != $user->company_id) {
                    $validationErrors['company_id'] = ['Anda hanya dapat membuat user untuk perusahaan Anda sendiri'];
                } else {
                    $companyId = $user->company_id;
                }
            }

            $selectedRole = Role::find($request->role_id);
            if ($selectedRole) {
                if ($user->hasRole('super-admin')) {
                } elseif ($userRoleType === 'warehouse') {
                    if ($selectedRole->type !== 'warehouse') {
                        $validationErrors['role_id'] = ['Anda hanya dapat menetapkan role warehouse'];
                    }
                } elseif ($userRoleType === 'company') {
                    if ($selectedRole->type !== 'company') {
                        $validationErrors['role_id'] = ['Anda hanya dapat menetapkan role company'];
                    }
                }
            }

            if (!empty($validationErrors)) {
                DB::rollBack();
                $firstError = collect($validationErrors)->first();
                $errorMessage = $firstError[0] ?? 'Validasi gagal';
                return $this->errorResponse($errorMessage, 422);
            }

            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $companyId,
            ]);

            $role = Role::find($request->role_id);
            if ($role) {
                $newUser->assignRole($role);
            }

            DB::commit();
            return $this->successResponse(null, 'User berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat user: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat user');
        }
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            $currentUserRole = $currentUser->roles->first();
            $currentUserRoleType = $currentUserRole ? $currentUserRole->type : null;

            if ($currentUser->hasRole('super-admin')) {
            } elseif ($currentUserRoleType === 'warehouse') {
                $targetUserRole = $user->roles->first();
                if (!$targetUserRole || $targetUserRole->type !== 'warehouse') {
                    return $this->forbiddenResponse('Anda hanya dapat mengedit user warehouse');
                }
            } elseif ($currentUserRoleType === 'company') {
                if ($user->company_id !== $currentUser->company_id) {
                    return $this->forbiddenResponse('Anda hanya dapat mengedit user dari perusahaan Anda sendiri');
                }
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk mengedit user');
            }

            $validationErrors = [];

            if ($currentUser->hasRole('super-admin')) {
                $companyId = $request->company_id;
            } elseif ($currentUserRoleType === 'warehouse') {
                if ($request->filled('company_id')) {
                    $validationErrors['company_id'] = ['Warehouse tidak perlu memasukkan company_id'];
                }
                $companyId = null;
            } elseif ($currentUserRoleType === 'company') {
                if (!$request->filled('company_id')) {
                    $validationErrors['company_id'] = ['Company wajib memasukkan company_id'];
                } elseif ($request->company_id != $currentUser->company_id) {
                    $validationErrors['company_id'] = ['Anda hanya dapat mengedit user untuk perusahaan Anda sendiri'];
                } else {
                    $companyId = $currentUser->company_id;
                }
            }

            $selectedRole = Role::find($request->role_id);
            if ($selectedRole) {
                if ($currentUser->hasRole('super-admin')) {
                } elseif ($currentUserRoleType === 'warehouse') {
                    if ($selectedRole->type !== 'warehouse') {
                        $validationErrors['role_id'] = ['Anda hanya dapat menetapkan role warehouse'];
                    }
                } elseif ($currentUserRoleType === 'company') {
                    if ($selectedRole->type !== 'company') {
                        $validationErrors['role_id'] = ['Anda hanya dapat menetapkan role company'];
                    }
                }
            }

            if (!empty($validationErrors)) {
                DB::rollBack();
                $firstError = collect($validationErrors)->first();
                $errorMessage = $firstError[0] ?? 'Validasi gagal';
                return $this->errorResponse($errorMessage, 422);
            }

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'company_id' => $companyId,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role]);
            }

            DB::commit();
            return $this->successResponse(null, 'User berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat memperbarui user: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat memperbarui user');
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            $currentUser = request()->user();
            $currentUserRole = $currentUser->roles->first();
            $currentUserRoleType = $currentUserRole ? $currentUserRole->type : null;

            if ($currentUser->hasRole('super-admin')) {
            } elseif ($currentUserRoleType === 'warehouse') {
                $targetUserRole = $user->roles->first();
                if (!$targetUserRole || $targetUserRole->type !== 'warehouse') {
                    return $this->forbiddenResponse('Anda hanya dapat menghapus user warehouse');
                }
            } elseif ($currentUserRoleType === 'company') {
                if ($user->company_id !== $currentUser->company_id) {
                    return $this->forbiddenResponse('Anda hanya dapat menghapus user dari perusahaan Anda sendiri');
                }
            } else {
                return $this->forbiddenResponse('Anda tidak memiliki akses untuk menghapus user');
            }

            if ($user->id === $currentUser->id) {
                return $this->forbiddenResponse('Anda tidak dapat menghapus akun Anda sendiri');
            }

            $user->delete();

            DB::commit();
            return $this->successResponse(null, 'User berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menghapus user: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat menghapus user');
        }
    }
}
