<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Motorizado extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'motorizados';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'password',
        'foto',
        'estado',
        'verificado',
        'activo',
        'lat',
        'lng',
        'ultimo_ping',
        'push_token',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'verificado'   => 'boolean',
        'activo'       => 'boolean',
        'lat'          => 'float',
        'lng'          => 'float',
        'ultimo_ping'  => 'datetime',
    ];

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public function despachosHoy()
    {
        return $this->despachos()
            ->whereDate('created_at', today())
            ->where('estado', 'entregado');
    }

    public function isDisponible(): bool
    {
        return $this->estado === 'disponible' && $this->activo;
    }
}
