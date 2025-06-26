<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $users = User::all();
        return $this->successResponse($users, 'User berhasil diambil');
    }


    public function store(UserStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $request->company_id,
            ]);
            $user->assignRole('admin-mitra');
            DB::commit();
            return $this->successResponse(null, 'User berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat membuat user: ' . $e->getMessage());
            DB::rollBack();
            return $this->serverErrorResponse('Terjadi kesalahan saat membuat user');
        }
    }
}
