<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Return JSON for API routes
        if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            $statusCode = 500;
            if ($this->isHttpException($exception)) {
                $statusCode = $exception->getStatusCode();
            } elseif (method_exists($exception, 'getStatusCode')) {
                $statusCode = $exception->getStatusCode();
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'An error occurred',
                'error' => config('app.debug') ? [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ] : null
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }
}

