<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    protected $table = 'comisiones';

    protected $fillable = [
        'order_id',
        'monto_pedido',
        'monto_comision',
        'fecha',
        'cobrado',
        'cobrado_at',
    ];

    protected $casts = [
        'monto_pedido'   => 'decimal:2',
        'monto_comision' => 'decimal:2',
        'fecha'          => 'date',
        'cobrado'        => 'boolean',
        'cobrado_at'     => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
