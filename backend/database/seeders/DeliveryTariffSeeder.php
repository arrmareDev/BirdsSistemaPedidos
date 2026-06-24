<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryTariff;

class DeliveryTariffSeeder extends Seeder
{
    public function run(): void
    {
        DeliveryTariff::truncate();

        $tarifas = [
            ['distancia_max_km' => 1.5, 'precio' => 5.00,  'orden' => 1],
            ['distancia_max_km' => 2.2, 'precio' => 6.00,  'orden' => 2],
            ['distancia_max_km' => 2.7, 'precio' => 7.00,  'orden' => 3],
            ['distancia_max_km' => 3.2, 'precio' => 8.00,  'orden' => 4],
            ['distancia_max_km' => 4.0, 'precio' => 9.00,  'orden' => 5],
            ['distancia_max_km' => 5.0, 'precio' => 10.00, 'orden' => 6],
            ['distancia_max_km' => 6.0, 'precio' => 11.00, 'orden' => 7],
            ['distancia_max_km' => 7.0, 'precio' => 13.00, 'orden' => 8],
        ];

        foreach ($tarifas as $tarifa) {
            DeliveryTariff::create([...$tarifa, 'activo' => true]);
        }
    }
}
