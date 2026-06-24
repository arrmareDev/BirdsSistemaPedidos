<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = [
        'fecha',
        'monto_apertura',
        'monto_cierre',
        'estado',
        'user_id',
        'abierta_at',
        'cerrada_at',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'monto_apertura' => 'decimal:2',
        'monto_cierre'  => 'decimal:2',
        'abierta_at'    => 'datetime',
        'cerrada_at'    => 'datetime',
    ];

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function getTotalVentasAttribute(): float
    {
        return $this->movimientos()->where('type', 'venta')->sum('amount');
    }

    public function getTotalGastosAttribute(): float
    {
        return $this->movimientos()->where('type', 'gasto')->sum('amount');
    }

    public function getTotalIngresosAttribute(): float
    {
        return $this->movimientos()->where('type', 'ingreso')->sum('amount');
    }

    public function getSaldoAttribute(): float
    {
        return $this->monto_apertura
            + $this->total_ventas
            + $this->total_ingresos
            - $this->total_gastos;
    }
}
