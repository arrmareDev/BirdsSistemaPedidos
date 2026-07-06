<?php

namespace App\Enums;

enum SaleChannel: string
{
    case Local   = 'local';
    case Recoger = 'recoger';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Local    => 'Consumo en local',
            self::Recoger  => 'Para llevar',
            self::Delivery => 'Delivery',
        };
    }

    public function requiresDeliveryFee(): bool
    {
        return $this === self::Delivery;
    }

    public function requiresMesa(): bool
    {
        return $this === self::Local;
    }

    public function requiresAddress(): bool
    {
        return $this === self::Delivery;
    }
}
