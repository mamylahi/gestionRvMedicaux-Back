<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        if ($exception instanceof ValidationException) {
            $allErrors = collect($exception->errors())->flatten()->implode(', ');
            return ApiResponse::error(
                'Validation échouée',
                422,
                $exception->errors(),
                $allErrors
            );
        }

        // ✅ AJOUT : Gestion spécifique des erreurs de base de données
        if ($exception instanceof QueryException) {
            // Log l'erreur complète pour le débogage
            \Log::error('Database Query Error', [
                'message' => $exception->getMessage(),
                'sql' => $exception->getSql() ?? 'N/A',
                'bindings' => $exception->getBindings() ?? [],
                'code' => $exception->getCode()
            ]);

            // Retourner une réponse avec le bon code HTTP
            return ApiResponse::error(
                config('app.debug')
                    ? 'Erreur de base de données: ' . $exception->getMessage()
                    : 'Erreur lors du traitement de la requête',
                500, // ✅ Code HTTP valide
                [],
                config('app.debug') ? $exception->getCode() : null // Code SQL en debug uniquement
            );
        }

        // Pour les erreurs HTTP (404, 403, etc.)
        if ($this->isHttpException($exception)) {
            $code = $exception->getStatusCode();

            return ApiResponse::error(
                $exception->getMessage() ?: 'Erreur HTTP',
                $code
            );
        }

        // ✅ CORRECTION : Pour toutes les autres exceptions
        // S'assurer que le code est un entier HTTP valide
        $code = 500; // Par défaut

        if (method_exists($exception, 'getCode')) {
            $exceptionCode = $exception->getCode();
            // Vérifier si c'est un code HTTP valide (entre 100 et 599)
            if (is_int($exceptionCode) && $exceptionCode >= 100 && $exceptionCode <= 599) {
                $code = $exceptionCode;
            }
        }

        return ApiResponse::error(
            config('app.debug')
                ? $exception->getMessage()
                : 'Erreur interne',
            $code,
            [],
            config('app.debug') ? [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'original_code' => $exception->getCode()
            ] : null
        );
    }
}
