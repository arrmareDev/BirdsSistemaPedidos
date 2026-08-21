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
    // Solo ventas en efectivo (o sin método registrado — los movimientos
    // manuales sin pedido asociado se asumen efectivo). Ya no se usa
    // para el cuadre de caja (ver getSaldoAttribute), pero se mantiene
    // para mostrar el desglose informativo "Ventas en efectivo" en el
    // resumen.
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

    // Todas las ventas, sin importar el método de pago.
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

    // El cajero registra TODO en caja sin importar el método de pago —
    // Yape, tarjeta y anticipado se verifican aparte (app de Yape, POS,
    // etc.) para que la caja cuadre contra el total real del día, no
    // solo contra el efectivo físico.
    public function getSaldoAttribute(): float
    {
        return $this->monto_apertura
            + $this->total_ventas_todas
            + $this->total_ingresos
            - $this->total_gastos;
    }

    // Desglose de ventas por método de pago — para que al cerrar caja el
    // cajero sepa exactamente cuánto debe encontrar en cada canal
    // (efectivo en el cajón, saldo en la cuenta de Yape, lo cobrado por
    // POS, etc.), en vez de un solo número "todos los métodos" sin
    // detalle. Agrupa variantes históricas del campo bajo la misma
    // etiqueta que ya usa la UI (metodoPagoLabel en el frontend).
    public function getVentasPorMetodoAttribute(): array
    {
        $porMetodo = $this->movimientos()
            ->where('type', 'venta')
            ->where('anulado', false)
            ->selectRaw('metodo_pago, SUM(amount) as total')
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago');

        $grupos = ['efectivo' => 0.0, 'yape' => 0.0, 'tarjeta' => 0.0, 'anticipado' => 0.0];

        foreach ($porMetodo as $metodo => $total) {
            $grupo = match ($metodo) {
                null, 'efectivo', 'contraentrega_efectivo' => 'efectivo',
                'yape', 'contraentrega_yape' => 'yape',
                'tarjeta' => 'tarjeta',
                'anticipado' => 'anticipado',
                default => 'efectivo',
            };
            $grupos[$grupo] += (float) $total;
        }

        return $grupos;
    }
}
