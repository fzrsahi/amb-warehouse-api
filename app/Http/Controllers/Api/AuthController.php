<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return $this->unauthorizedResponse('Email atau password salah');
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
            ], 'Login berhasil');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Validasi gagal');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Terjadi kesalahan saat login');
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse(null, 'Logout berhasil');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Terjadi kesalahan saat logout');
        }
    }

    public function session(Request $request)
    {
        $user = $request->user();

        $response = [
            'name' => $user->name,
            'role' => $user->roles->pluck('name'),
            'email' => $user->email,
            'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
        ];

        if ($user->company_id) {
            $response['company'] = $user->company;
        }

        return $this->successResponse($response, 'Session berhasil diambil');
    }
}
