<?php

namespace App\Models;

use App\Enums\SaleChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'client_name',
        'client_phone',
        'type',           // local | recoger | delivery
        'status',
        'codigo',
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

    protected static function boot()
    {
        parent::boot();

        // Identificador de cara al cliente/staff — un número aleatorio de
        // 5 cifras, no el id incremental de la base de datos. Se reintenta
        // si por casualidad ya existe (con 90 000 combinaciones posibles,
        // es rarísimo, pero mejor cubrirlo).
        static::creating(function (Order $order) {
            if ($order->codigo) return;

            do {
                $codigo = random_int(10000, 99999);
            } while (static::where('codigo', $codigo)->exists());

            $order->codigo = $codigo;
        });
    }

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

    // Nota: la columna se llama delivery_zone_id por historia, pero en
    // realidad referencia una DeliveryTariff (tarifa por distancia) — es
    // lo que usa el checkout real. Antes apuntaba a DeliveryZone (tabla
    // vieja de distritos, nunca poblada desde el flujo real).
    public function deliveryTariff()
    {
        return $this->belongsTo(DeliveryTariff::class, 'delivery_zone_id');
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
