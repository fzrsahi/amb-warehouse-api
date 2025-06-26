<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\User;

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $users = User::all();
        return $this->successResponse($users, 'User berhasil diambil');
    }


    public function store($id) {}
}
