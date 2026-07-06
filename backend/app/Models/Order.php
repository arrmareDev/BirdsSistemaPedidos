<?php

namespace App\Models;

use App\Enums\SaleChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_name',
        'client_phone',
        'type',           // local | recoger | delivery
        'status',
        'address',
        'reference',
        'district',
        'delivery_zone_id',
        'delivery_fee',
        'mesa',
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

    protected function casts(): array
    {
        return [
            'type'               => SaleChannel::class,
            'subtotal'           => 'decimal:2',
            'delivery_fee'       => 'decimal:2',
            'total'              => 'decimal:2',
            'entrega_programada' => 'boolean',
            'fecha_entrega'      => 'date:Y-m-d',
            'hora_entrega'       => 'string',
        ];
    }

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

    public function scopeOfChannel($query, SaleChannel $channel)
    {
        return $query->where('type', $channel->value);
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

        // Pedidos locales/para llevar no pasan por "en_camino"
        if ($this->type !== SaleChannel::Delivery && $this->status === 'listo') {
            return 'entregado';
        }

        return $flow[$this->status] ?? 'entregado';
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['entregado', 'cancelado']);
    }
}
