<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrasi middleware Anda di sini jika ada.
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Ini adalah satu-satunya pusat penanganan error untuk API Anda.
        $exceptions->render(function (Throwable $e, Request $request) {
            // Hanya jalankan logika ini jika request ditujukan untuk API.
            if ($request->is('api/*') || $request->expectsJson()) {

                // Siapkan variabel default
                $statusCode = 500;
                $message = 'Terjadi kesalahan pada server.';

                // Tentukan status code dan pesan berdasarkan jenis Exception
                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    // Ambil pesan error validasi pertama
                    $message = collect($e->errors())->first()[0] ?? 'Data yang diberikan tidak valid.';
                } elseif ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Sesi Tidak Valid';
                } elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $statusCode = 403;
                    $message = 'Anda tidak memiliki hak akses untuk melakukan aksi ini.';
                } elseif ($e instanceof ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Data tidak ditemukan.';
                } elseif ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                $message = 'Data tidak ditemukan.';
                }

            if (config('app.debug') && $statusCode === 500 && !empty($e->getMessage())) {
                    $message = $e->getMessage();
                }

            return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data'    => null,
                ], $statusCode);
            }
        });
    })->create();
