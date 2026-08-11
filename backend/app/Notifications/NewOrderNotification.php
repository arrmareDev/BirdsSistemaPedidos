<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderNotification extends Notification
{
    public function __construct(private Order $order) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $tipoLabel = match ($this->order->type) {
            'delivery' => 'Delivery',
            'recoger'  => 'Para recoger',
            'local'    => 'En local',
            default    => $this->order->type,
        };

        return (new WebPushMessage)
            ->title('🔔 Nuevo pedido — ' . $tipoLabel)
            ->icon('/logobirds.png')
            ->body("{$this->order->client_name} · S/ " . number_format((float) $this->order->total, 2))
            ->action('Ver pedido', 'ver_pedido')
            ->data(['url' => '/admin/pedidos'])
            ->options(['TTL' => 300]);
    }
}
