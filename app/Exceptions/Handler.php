<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
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
        // Gestion des erreurs d'authentification
        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        // Gestion des erreurs de validation
        if ($exception instanceof ValidationException ) {
            $allErrors = collect($exception->errors())
                ->flatten()
                ->implode(', ');
            return ApiResponse::error(
                'Validation échouée',
                422,
                $exception->errors(),
                $allErrors
            );
        }

        // Pour les erreurs HTTP (404, 403, etc.)
        if ($this->isHttpException($exception)) {
            $code = $exception->getCode();
            return ApiResponse::error($exception->getMessage() ?: 'Erreur HTTP', $code);
        }

        // Pour toutes les autres exceptions
        return ApiResponse::error(
            $exception->getMessage() ?: 'Erreur interne',
            method_exists($exception, 'getCode') && $exception->getCode() ? $exception->getCode() : 500
        );
    }
}
