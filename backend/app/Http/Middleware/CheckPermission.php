<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $views): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        // Soporta alternativas: permission:catalog|orders → basta con tener una de las dos
        $opciones = explode('|', $views);
        $tienePermiso = collect($opciones)->contains(fn($v) => $user->hasViewAccess($v));

        if (!$tienePermiso) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a este módulo'], 403);
        }

        return $next($request);
    }
}
