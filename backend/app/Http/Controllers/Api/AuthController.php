<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // ── Rate limiting — máx 5 intentos por IP en 1 minuto ──
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error(
                "Demasiados intentos. Espera {$seconds} segundos.",
                429
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return $this->error('Credenciales incorrectas', 401);
        }

        // ── Solo roles válidos pueden ingresar ────────────
        if (!in_array($user->role, User::ROLES)) {
            return $this->error('Acceso no autorizado', 403);
        }

        RateLimiter::clear($key);

        // Revocar tokens anteriores
        $user->tokens()->delete();

        $abilities = $this->abilitiesForRole($user->role);
        $token     = $user->createToken('admin-token', $abilities)->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 'Login exitoso');
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();
        return $this->success(null, 'Sesión cerrada');
    }

    public function me(): JsonResponse
    {
        return $this->success($this->formatUser(auth()->user()));
    }

    // ── Helpers ───────────────────────────────────────────

    private function formatUser(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
            'permissions' => [
                // ── Navegación (según vistas habilitadas para este usuario) ──
                'dashboard' => $user->canViewDashboard(),
                'catalog'   => $user->canViewCatalog(),
                'orders'    => $user->canManageOrders(),
                'caja'      => $user->canManageCaja(),
                'clients'   => $user->canViewClients(),
                'reports'   => $user->canViewReports(),
                'users'     => $user->canViewUsers(),
                'sistema'   => $user->canViewSistema(),

                // ── Escritura (ligada al rol, no a los checkboxes de vista) ──
                'can_manage_catalog' => $user->canManageCatalog(),      // solo admin/sistema
                'can_manage_users'   => $user->canManageUsers(),        // solo admin/sistema
                'can_cobrar'         => $user->hasRole(['admin', 'sistema']),
                'can_delete' => !$user->isSalon(), // ← antes excluía también a atención
                'can_write_orders'   => $user->canWriteOrders(),        // false solo para 'salon'
            ],
        ];
    }

    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => [
                'dashboard',
                'catalog',
                'orders',
                'caja',
                'clients',
                'reports',
                'users',
                'sistema',
            ],
            'contador' => [
                'dashboard',
                'catalog:view', // ← solo lectura, no gestiona catálogo
                'orders',
                'caja',
                'clients',
                'reports',
                'sistema',
                // ← sin 'users'
            ],
            'atencion' => [
                'orders',
                'catalog:view', // ← necesita leer productos para armar pedidos
            ],
            'salon' => [
                'orders:view', // ← solo lectura, no puede crear ni cambiar estados
            ],
            'sistema' => [
                'dashboard',
                'catalog',
                'orders',
                'caja',
                'clients',
                'reports',
                'users',
                'sistema',
                'cobrar',
            ],
            default => [],
        };
    }
}
