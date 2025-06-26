<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;

class RoleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $roles = Role::all();
        return $this->successResponse($roles, 'Roles berhasil diambil');
    }
}
