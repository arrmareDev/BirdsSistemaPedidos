<?php

namespace App\Enums;

enum BusinessLine: string
{
    case Floreria  = 'floreria';
    case Cafeteria = 'cafeteria';
    case Menu      = 'menu';

    public function label(): string
    {
        return match ($this) {
            self::Floreria  => 'Florería',
            self::Cafeteria => 'Cafetería',
            self::Menu      => 'Menú',
        };
    }
}
