<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'fecha',
        'monto_apertura',
        'monto_cierre',
        'monto_contado',
        'diferencia',
        'motivo_diferencia',
        'estado',
        'user_id',
        'cerrado_por',
        'abierta_at',
        'cerrada_at',
        'motivo_reapertura',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'monto_apertura' => 'decimal:2',
        'monto_cierre'  => 'decimal:2',
        'monto_contado' => 'decimal:2',
        'diferencia'    => 'decimal:2',
        'abierta_at'    => 'datetime',
        'cerrada_at'    => 'datetime',
    ];

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function abiertaPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cerradaPor()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    // Los movimientos anulados no cuentan para ningún total — quedan
    // en el registro (nunca se borran), pero no afectan el saldo.
    // Este es el que cuenta para el cuadre físico (el saldo esperado
    // al cerrar) — solo efectivo. Los movimientos manuales (sin pedido
    // asociado, metodo_pago = null) se asumen efectivo, porque es lo
    // único que tiene sentido cuando alguien anota una venta a mano.
    public function getTotalVentasAttribute(): float
    {
        return $this->movimientos()
            ->where('type', 'venta')
            ->where('anulado', false)
            ->where(function ($q) {
                $q->whereNull('metodo_pago')->orWhere('metodo_pago', 'efectivo');
            })
            ->sum('amount');
    }

    // Todas las ventas, sin importar el método de pago — solo para
    // mostrar el panorama completo, nunca se usa en el cálculo del
    // saldo/cuadre.
    public function getTotalVentasTodasAttribute(): float
    {
        return $this->movimientos()->where('type', 'venta')->where('anulado', false)->sum('amount');
    }

    public function getTotalGastosAttribute(): float
    {
        return $this->movimientos()->where('type', 'gasto')->where('anulado', false)->sum('amount');
    }

    public function getTotalIngresosAttribute(): float
    {
        return $this->movimientos()->where('type', 'ingreso')->where('anulado', false)->sum('amount');
    }

    public function getSaldoAttribute(): float
    {
        return $this->monto_apertura
            + $this->total_ventas
            + $this->total_ingresos
            - $this->total_gastos;
    }
}
