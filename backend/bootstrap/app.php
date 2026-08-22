<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sin esto, Laravel nunca se entera de que nginx recibe las
        // peticiones por HTTPS — genera enlaces (imágenes, storage,
        // etc.) con http:// aunque el sitio real sea https://.
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
        // ── Alias de roles aquí dentro, no en un bloque separado ──
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'require-password-change' => \App\Http\Middleware\RequirePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // El mensaje default de Laravel para 429 ("Too Many Attempts.")
        // sale en inglés — el resto de la API responde en español con
        // el formato {success, message}, así que este debía coincidir.
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

                return response()->json([
                    'success' => false,
                    'message' => $retryAfter
                        ? "Demasiados intentos — espera {$retryAfter} segundos antes de volver a intentar."
                        : 'Demasiados intentos — espera un momento antes de volver a intentar.',
                ], 429, $e->getHeaders());
            }
        });
    })->create();
