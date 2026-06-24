<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTariff extends Model
{
    protected $table = 'delivery_tariffs';

    protected $fillable = ['distancia_max_km', 'precio', 'orden', 'activo'];

    protected $casts = [
        'distancia_max_km' => 'float',
        'precio'           => 'float',
        'orden'            => 'integer',
        'activo'           => 'boolean',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }
}
