<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\InvoiceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/session', [AuthController::class, 'session']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::post('/companies', [CompanyController::class, 'store'])->middleware('can:create company');
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('can:view all company');
    Route::get('/my-company', [CompanyController::class, 'myCompany'])->middleware('can:view own company');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->middleware('can:show company');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->middleware('can:edit company');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->middleware('can:delete company');


    Route::post('/users', [UserController::class, 'store'])->middleware('can:create user');
    Route::get('/users', [UserController::class, 'index'])->middleware('can:view all user');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:show user');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:edit user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:delete user');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:view all role');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('can:create role');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('can:show role');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('can:edit role');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:delete role');

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:view all permission');


    Route::get('/airlines', [AirlineController::class, 'index'])->middleware('can:view all airline');
    Route::get('/airlines/{airline}', [AirlineController::class, 'show'])->middleware('can:show airline');
    Route::post('/airlines', [AirlineController::class, 'store'])->middleware('can:create airline');
    Route::put('/airlines/{airline}', [AirlineController::class, 'update'])->middleware('can:edit airline');
    Route::delete('/airlines/{airline}', [AirlineController::class, 'destroy'])->middleware('can:delete airline');


    Route::get('/locations', [LocationController::class, 'index'])->middleware('can:view all location');
    Route::get('/locations/{location}', [LocationController::class, 'show'])->middleware('can:show location');
    Route::post('/locations', [LocationController::class, 'store'])->middleware('can:create location');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->middleware('can:edit location');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->middleware('can:delete location');


    Route::get('/deposits', [DepositController::class, 'index'])->middleware('can:view all deposit');
    Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->middleware('can:show deposit');
    Route::post('/deposits', [DepositController::class, 'store'])->middleware('can:create deposit');
    Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->middleware('can:edit deposit');
    Route::delete('/deposits/{deposit}', [DepositController::class, 'destroy'])->middleware('can:delete deposit');

    Route::post('/deposits/{deposit}/verify', [DepositController::class, 'verify'])->middleware('can:verify deposit');

    Route::get('/flights', [FlightController::class, 'index'])->middleware('can:view all flight');
    Route::get('/flights/{flight}', [FlightController::class, 'show'])->middleware('can:show flight');
    Route::post('/flights', [FlightController::class, 'store'])->middleware('can:create flight');
    Route::put('/flights/{flight}', [FlightController::class, 'update'])->middleware('can:edit flight');
    Route::delete('/flights/{flight}', [FlightController::class, 'destroy'])->middleware('can:delete flight');


    Route::get('/items', [ItemController::class, 'index'])->middleware('can:view all item');
    Route::get('/items/{item}', [ItemController::class, 'show'])->middleware('can:show item');
    Route::post('/items', [ItemController::class, 'store'])->middleware('can:create item');
    Route::put('/items/{item}', [ItemController::class, 'update'])->middleware('can:edit item');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->middleware('can:delete item');
    Route::post('/items/{item}/verify', [ItemController::class, 'verify'])->middleware('can:verify item');
    Route::post('/items/out', [ItemController::class, 'out'])->middleware('can:out item');

    Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('can:view all invoice');
    Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('can:create invoice');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('can:show invoice');
});
