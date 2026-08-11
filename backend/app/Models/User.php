<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasPushSubscriptions;

    protected $fillable = ['name', 'email', 'password', 'role', 'permissions', 'must_change_password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at'    => 'datetime',
        'password'             => 'hashed',
        'permissions'          => 'array',
        'must_change_password' => 'boolean',
    ];

    const ROLES = ['admin', 'sistema', 'contador', 'atencion', 'salon'];
    const VIEWS = ['dashboard', 'catalog', 'orders', 'caja', 'clients', 'reports', 'users', 'sistema'];

    // ── Helpers de rol ────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isSistema(): bool
    {
        return $this->role === 'sistema';
    }
    public function isContador(): bool
    {
        return $this->role === 'contador';
    }
    public function isAtencion(): bool
    {
        return $this->role === 'atencion';
    }
    public function isSalon(): bool
    {
        return $this->role === 'salon';
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    public function canDelete(User $target): bool
    {
        if (!$this->hasRole(['admin', 'sistema'])) return false;
        return $this->id !== $target->id;
    }

    // ── Vistas por defecto según rol ──────────────────────
    public static function defaultViewsForRole(string $role): array
    {
        return match ($role) {
            'admin', 'sistema' => self::VIEWS, // acceso total
            'contador' => ['dashboard', 'catalog', 'orders', 'caja', 'clients', 'reports', 'sistema'], // todo menos 'users'
            'atencion' => ['orders'],
            'salon'    => ['orders'],
            default    => ['dashboard'],
        };
    }

    public function allowedViews(): array
    {
        return $this->permissions ?? self::defaultViewsForRole($this->role);
    }

    public function hasViewAccess(string $view): bool
    {
        if ($view === 'sistema' && $this->hasRole(['admin', 'sistema'])) {
            return true;
        }
        return in_array($view, $this->allowedViews());
    }

    // ── Permisos de vista ──────────────────────────────────
    public function canViewDashboard(): bool
    {
        return $this->hasViewAccess('dashboard');
    }
    public function canViewCatalog(): bool
    {
        return $this->hasViewAccess('catalog');
    }
    public function canManageOrders(): bool
    {
        return $this->hasViewAccess('orders');
    }
    public function canManageCaja(): bool
    {
        return $this->hasViewAccess('caja');
    }
    public function canViewClients(): bool
    {
        return $this->hasViewAccess('clients');
    }
    public function canViewReports(): bool
    {
        return $this->hasViewAccess('reports');
    }
    public function canViewUsers(): bool
    {
        return $this->hasViewAccess('users');
    }
    public function canAccessSistema(): bool
    {
        return $this->hasViewAccess('sistema');
    }
    public function canViewSistema(): bool
    {
        return $this->hasViewAccess('sistema');
    }

    // ── Permisos de acción (ligados al rol, no a checkboxes) ──
    public function canManageCatalog(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }
    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema']);
    }

    // Salón solo lee pedidos — el resto de roles con acceso a "orders" sí pueden crear/editar
    public function canWriteOrders(): bool
    {
        return $this->hasViewAccess('orders') && !$this->isSalon();
    }
}
