<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET /admin/users
    public function index(): JsonResponse
    {
        $authUser  = auth()->user();
        $isSistema = $authUser->role === 'sistema';

        $query = User::orderBy('id');

        if (!$isSistema) {
            $query->where('role', '!=', 'sistema');
        }

        $users = $query->get()->map(fn($u) => [
            'id'          => $u->id,
            'name'        => $u->name,
            'email'       => $u->email,
            'role'        => $u->role,
            'permissions' => $u->allowedViews(), // ← NUEVO — vistas efectivas
            'can_reset'   => $isSistema,
            'created_at'  => $u->created_at?->toISOString(),
        ]);

        return $this->success($users);
    }

    // GET /admin/users/{id}
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return $this->success([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'permissions' => $user->allowedViews(),
            'created_at'  => $user->created_at?->toISOString(),
        ]);
    }

    // POST /admin/users
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'contador', 'atencion', 'salon'])], // ← quitado super_admin/cajero, sin 'sistema' (se crea solo por seed)
            'permissions'   => 'nullable|array',
            'permissions.*' => Rule::in(User::VIEWS),
        ]);

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'permissions' => $data['permissions'] ?? null,
        ]);

        return $this->created([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'permissions' => $user->allowedViews(),
            'created_at'  => $user->created_at?->toISOString(),
        ], 'Usuario creado');
    }

    // PUT /admin/users/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'email'         => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'password'      => 'sometimes|nullable|string|min:8',
            'role' => ['sometimes', Rule::in(['admin', 'contador', 'atencion', 'salon'])], // ← quitado super_admin/cajero, sin 'sistema' (se crea solo por seed)
            'permissions'   => 'nullable|array',
            'permissions.*' => Rule::in(User::VIEWS),
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->success([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'permissions' => $user->allowedViews(),
            'created_at'  => $user->created_at?->toISOString(),
        ], 'Usuario actualizado');
    }

    // POST /admin/users/{id}/reset-password
    public function resetPassword(int $id): JsonResponse
    {
        $actor = auth()->user();

        if (!$actor->hasRole(['admin', 'sistema'])) {
            return $this->error('No tienes permiso para esta acción', 403);
        }

        $user = User::findOrFail($id);

        // Contraseña temporal con entropía real (no basada en datos públicos
        // como el nombre) — 12 caracteres alfanuméricos al azar.
        $tempPassword = Str::password(12, symbols: false);

        $user->update([
            'password'             => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);
        $user->tokens()->delete();

        Log::info('Contraseña reseteada por administrador', [
            'actor_id'    => $actor->id,
            'actor_email' => $actor->email,
            'target_id'   => $user->id,
            'target_email' => $user->email,
        ]);

        return $this->success([
            'user_id'       => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'temp_password' => $tempPassword,
        ], 'Contraseña reseteada. Comparte esta contraseña temporal con el usuario — deberá cambiarla al iniciar sesión.');
    }

    // DELETE /admin/users/{id}
    public function destroy(User $user)
    {
        if (!auth()->user()->canDelete($user)) {
            abort(403, 'No puedes eliminar este usuario.');
        }

        $user->delete();
        return response()->noContent();
    }
}
