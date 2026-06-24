<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_name',
        'client_phone',
        'type',           // recoger | delivery
        'status',
        'address',
        'reference',
        'district',
        'delivery_zone_id',
        'delivery_fee',
        'note',
        'mensaje_tarjeta',
        'fecha_entrega',
        'hora_entrega',
        'entrega_programada',
        'metodo_pago',
        'lat',
        'lng',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'subtotal'           => 'decimal:2',
        'delivery_fee'       => 'decimal:2',
        'total'              => 'decimal:2',
        'entrega_programada' => 'boolean',
        'fecha_entrega'      => 'date:Y-m-d',
        'hora_entrega'       => 'string',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function comision()
    {
        return $this->hasOne(Comision::class);
    }

    public function getNextStatus(): string
    {
        $flow = [
            'nuevo'      => 'confirmado',
            'confirmado' => 'preparando',
            'preparando' => 'listo',
            'listo'      => 'en_camino',
            'en_camino'  => 'entregado',
        ];

        return $flow[$this->status] ?? 'entregado';
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['entregado', 'cancelado']);
    }
}
