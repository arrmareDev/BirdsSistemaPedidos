<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'role', 'permissions'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'permissions'       => 'array',
    ];

    const ROLES = ['super_admin', 'admin', 'cajero', 'sistema'];

    // Vistas disponibles en el sistema (deben coincidir con AdminShell.vue)
    const VIEWS = ['dashboard', 'catalog', 'orders', 'caja', 'clients', 'reports', 'users', 'sistema'];

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

    public function canDelete(User $target): bool
    {
        if (!$this->hasRole(['admin', 'sistema', 'super_admin'])) return false;
        return $this->id !== $target->id;
    }

    // ── Vistas por defecto según rol (usadas cuando el usuario
    //    no tiene permisos personalizados asignados) ──────────
    public static function defaultViewsForRole(string $role): array
    {
        return match ($role) {
            'super_admin', 'sistema' => self::VIEWS, // acceso total
            'admin'  => ['dashboard', 'catalog', 'orders', 'caja', 'clients', 'reports', 'users'],
            'cajero' => ['dashboard', 'catalog', 'orders', 'caja', 'clients'],
            default  => ['dashboard'],
        };
    }

    // ── Vistas efectivas de este usuario ──────────────────
    // Si tiene `permissions` personalizado, se usa eso.
    // Si no (null), cae al set por defecto de su rol.
    public function allowedViews(): array
    {
        return $this->permissions ?? self::defaultViewsForRole($this->role);
    }

    public function hasViewAccess(string $view): bool
    {
        // super_admin y sistema siempre tienen acceso a Sistema,
        // sin importar lo que digan sus permisos personalizados
        if ($view === 'sistema' && ($this->isSuperAdmin() || $this->isSistema())) {
            return true;
        }

        return in_array($view, $this->allowedViews());
    }

    // ── Permisos de vista (usados por frontend y middleware) ──
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

    // ── Permisos de escritura/acción (siguen ligados al rol,
    //    no son checkboxes de vista — ej: un cajero nunca gestiona
    //    catálogo aunque tenga la vista de catálogo habilitada) ──
    public function canManageCatalog(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }
    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'sistema', 'super_admin']);
    }
}
