<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $table = 'despachos';

    protected $fillable = [
        'order_id',
        'motorizado_id',
        'estado',
        'comision_motorizado',
        'nota_motorizado',
        'solicitado_at',
        'aceptado_at',
        'recogido_at',
        'entregado_at',
    ];

    protected $casts = [
        'comision_motorizado' => 'decimal:2',
        'solicitado_at'       => 'datetime',
        'aceptado_at'         => 'datetime',
        'recogido_at'         => 'datetime',
        'entregado_at'        => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }
}
