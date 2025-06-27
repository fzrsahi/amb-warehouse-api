<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AirlineController;

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
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->middleware('can:delete company');


    Route::post('/users', [UserController::class, 'store'])->middleware('can:create user');
    Route::get('/users', [UserController::class, 'index'])->middleware('can:view all users');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:edit user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:delete user');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:view all roles');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('can:create role');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('can:view role');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('can:edit role');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:delete role');

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:view all permissions');


    Route::get('/airlines', [AirlineController::class, 'index'])->middleware('can:view all airlines');
    Route::post('/airlines', [AirlineController::class, 'store'])->middleware('can:create airline');
    Route::put('/airlines/{airline}', [AirlineController::class, 'update'])->middleware('can:edit airline');
    Route::delete('/airlines/{airline}', [AirlineController::class, 'destroy'])->middleware('can:delete airline');
});
