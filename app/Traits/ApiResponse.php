<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Membangun respons sukses.
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200, $pagination = null): JsonResponse
    {
        if ($pagination) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $data,
                'pagination' => $pagination,
            ], $code);
        } else {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $data,
            ], $code);
        }
    }

    /**
     * Membangun respons error.
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Respons untuk 404 Not Found.
     */
    protected function notFoundResponse(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Respons untuk 401 Unauthorized (otentikasi gagal).
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Respons untuk 403 Forbidden (otorisasi/hak akses gagal).
     */
    protected function forbiddenResponse(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Respons untuk 422 Unprocessable Entity (validasi gagal).
     */
    protected function validationErrorResponse($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        // Ambil pesan error pertama dari array validasi
        $firstMessage = $message;
        if (is_array($errors) && !empty($errors)) {
            $firstError = collect($errors)->first();
            if (is_array($firstError) && !empty($firstError)) {
                $firstMessage = $firstError[0] ?? $message;
            }
        }
        return $this->errorResponse($firstMessage, 422);
    }

    /**
     * Respons untuk 500 Internal Server Error.
     */
    protected function serverErrorResponse(string $message = 'Terjadi kesalahan pada server'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}
