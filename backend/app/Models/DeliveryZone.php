<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $table = 'delivery_zones';

    protected $fillable = [
        'nombre',
        'precio',
        'activo',
        'orden',
        'poligono',
    ];

    protected $casts = [
        'precio'   => 'float',
        'activo'   => 'boolean',
        'orden'    => 'integer',
        'poligono' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    public function tienePoligono(): bool
    {
        return !empty($this->poligono) && count($this->poligono) >= 3;
    }
}
