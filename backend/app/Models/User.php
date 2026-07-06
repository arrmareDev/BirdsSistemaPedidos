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
    const ROLES = ['super_admin', 'admin', 'cajero', 'sistema'];

    // ── Helpers de rol ────────────────────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

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
    // super_admin tiene acceso a todo lo que tiene admin, más
    // capacidades exclusivas (gestión de usuarios sistema, etc.)

    public function canViewDashboard(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema', 'super_admin']);
    }

    public function canViewCatalog(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema', 'super_admin']);
    }

    public function canManageCatalog(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }

    public function canManageOrders(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema', 'super_admin']);
    }

    public function canManageCaja(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema', 'super_admin']);
    }

    public function canViewClients(): bool
    {
        return $this->hasRole(['cajero', 'admin', 'sistema', 'super_admin']);
    }

    public function canViewReports(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }

    public function canViewUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }

    public function canDelete(User $target): bool
    {
        if (!$this->hasRole(['admin', 'sistema', 'super_admin'])) {
            return false;
        }

        return $this->id !== $target->id; // nadie puede borrarse a sí mismo
    }

    public function canAccessSistema(): bool
    {
        return $this->isSistema() || $this->isSuperAdmin();
    }

    public function canViewSistema(): bool
    {
        return $this->isSistema() || $this->isSuperAdmin();
    }
}
