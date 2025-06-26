<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\RoleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/session', [AuthController::class, 'session']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::post('/companies', [CompanyController::class, 'store'])->middleware('can:create company');
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('can:view all companies');


    Route::post('/users', [UserController::class, 'store'])->middleware('can:create user');
    Route::get('/users', [UserController::class, 'index'])->middleware('can:view all users');


    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:view all roles');
});
