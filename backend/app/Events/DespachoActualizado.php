<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DespachoActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $despachoId,
        public int $orderId,
        public string $estado,
        public ?array $motorizado = null,
        public ?float $montoCobrado = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('admin.despachos')];
    }

    public function broadcastAs(): string
    {
        return 'despacho.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'despacho_id'   => $this->despachoId,
            'order_id'      => $this->orderId,
            'estado'        => $this->estado,
            'motorizado'    => $this->motorizado,
            'monto_cobrado' => $this->montoCobrado,
        ];
    }
}
