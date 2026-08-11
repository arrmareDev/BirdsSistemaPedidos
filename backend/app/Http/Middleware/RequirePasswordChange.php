<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePasswordChange
{
    // Rutas permitidas aunque haya un cambio de contraseña pendiente —
    // el usuario necesita poder cambiarla y salir, nada más.
    private const RUTAS_PERMITIDAS = [
        'api/v1/admin/auth/password',
        'api/v1/admin/auth/logout',
        'api/v1/admin/auth/me',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !in_array($request->path(), self::RUTAS_PERMITIDAS)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes cambiar tu contraseña antes de continuar',
                'must_change_password' => true,
            ], 403);
        }

        return $next($request);
    }
}
