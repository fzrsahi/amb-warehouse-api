<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/session', [AuthController::class, 'session']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::post('/companies', [CompanyController::class, 'store'])->middleware('can:create company');
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('can:view all companies');
    Route::get('/companies/my', [CompanyController::class, 'myCompany'])->middleware('can:view own company');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->middleware('can:edit company');


    Route::post('/users', [UserController::class, 'store'])->middleware('can:create user');
    Route::get('/users', [UserController::class, 'index'])->middleware('can:view all users');


    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:view all roles');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('can:create role');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('can:view role');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('can:edit role');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:delete role');

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:view all permissions');
});
