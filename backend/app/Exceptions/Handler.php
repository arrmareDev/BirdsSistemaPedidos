<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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

    public function render($request, Throwable $e)
    {
        // Solo para rutas API
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e): \Illuminate\Http\JsonResponse
    {
        $status  = 500;
        $message = 'Error interno del servidor';
        $errors  = null;

        match (true) {
            $e instanceof ValidationException => [
                $status  = 422,
                $message = 'Error de validación',
                $errors  = $e->errors(),
            ],
            $e instanceof AuthenticationException => [
                $status  = 401,
                $message = 'No autenticado',
            ],
            $e instanceof ModelNotFoundException => [
                $status  = 404,
                $message = 'Recurso no encontrado',
            ],
            $e instanceof NotFoundHttpException => [
                $status  = 404,
                $message = 'Ruta no encontrada',
            ],
            $e instanceof MethodNotAllowedHttpException => [
                $status  = 405,
                $message = 'Método no permitido',
            ],
            default => null,
        };

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if (config('app.debug') && $status === 500) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ];
        }

        return response()->json($response, $status);
    }
}
