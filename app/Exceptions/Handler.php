<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle API requests
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, Request $request)
    {
        // Authentication Exception (including Sanctum)
        if ($e instanceof AuthenticationException) {
            return $this->unauthorizedResponse('Token tidak valid atau tidak ditemukan');
        }

        // Validation Exception
        if ($e instanceof ValidationException) {
            return $this->validationErrorResponse($e->errors(), 'Validasi gagal');
        }

        // Model Not Found Exception
        if ($e instanceof ModelNotFoundException) {
            return $this->notFoundResponse('Data tidak ditemukan');
        }

        // Not Found HTTP Exception
        if ($e instanceof NotFoundHttpException) {
            return $this->notFoundResponse('Endpoint tidak ditemukan');
        }

        // Default error response
        if (config('app.debug')) {
            return $this->errorResponse($e->getMessage(), 500, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->serverErrorResponse();
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->unauthorizedResponse('Token tidak valid atau tidak ditemukan');
        }

        return redirect()->guest(route('login'));
    }
}
