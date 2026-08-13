<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'logo',
        'descripcion',
        'categoria',
        'url_externa',
        'descuento_texto',
        'clics',
        'sort_order',
        'activo',
    ];

    protected $appends = ['logo_url'];

    protected $casts = [
        'clics'      => 'integer',
        'sort_order' => 'integer',
        'activo'     => 'boolean',
    ];

    public function getLogoUrlAttribute(): string|null
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : null;
    }
}
