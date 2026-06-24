<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ── Roles válidos ─────────────────────────────────────
    const ROLES = ['cajero', 'admin', 'sistema'];

    // ── Helpers de rol ────────────────────────────────────
    public function isSistema(): bool
    {
        return $this->role === 'sistema';
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isCajero(): bool
    {
        return $this->role === 'cajero';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    // ── Permisos por módulo ───────────────────────────────

    // Dashboard — todos lo ven
    public function canViewDashboard(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema']);
    }

    // Catálogo — cajero y admin modifican, sistema solo ve
    public function canViewCatalog(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema']);
    }

    public function canManageCatalog(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }

    // Pedidos — todos
    public function canManageOrders(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema']);
    }

    // Caja — todos, sistema solo ve
    public function canManageCaja(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema']);
    }

    // Clientes — todos
    public function canViewClients(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema']);
    }

    // Reportes — admin y sistema
    public function canViewReports(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }

    // Usuarios — admin crea/edita/elimina, sistema solo ve y resetea
    public function canViewUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }

    // Sistema — solo sistema
    public function canAccessSistema(): bool
    {
        return $this->isSistema();
    }

    // Sistema puede ver módulo sistema en readonly
    public function canViewSistema(): bool
    {
        return $this->isSistema();
    }
}
