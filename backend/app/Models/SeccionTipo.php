<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeccionTipo extends Model
{
    protected $table = 'seccion_tipos';

    protected $fillable = ['nombre', 'icono', 'activo', 'sort_order'];

    protected $casts = [
        'activo'     => 'boolean',
        'sort_order' => 'integer',
    ];
}
