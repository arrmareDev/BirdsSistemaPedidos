<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            // ── Chiclayo Centro ───────────────────────────
            ['nombre' => 'Centro de Chiclayo',              'precio' => 5.00,  'orden' => 1],
            ['nombre' => 'Urb. San Juan',                   'precio' => 6.00,  'orden' => 2],
            ['nombre' => 'Urb. Campodónico',                'precio' => 7.00,  'orden' => 3],
            ['nombre' => 'Urb. Los Parques',                'precio' => 6.00,  'orden' => 4],
            ['nombre' => 'Urb. Patazca',                    'precio' => 6.00,  'orden' => 5],
            ['nombre' => 'Urb. La Primavera',               'precio' => 7.00,  'orden' => 6],
            ['nombre' => 'Urb. Santa Victoria',             'precio' => 6.00,  'orden' => 7],
            ['nombre' => 'Urb. Federico Villarreal',        'precio' => 7.00,  'orden' => 8],
            ['nombre' => 'Cond. El Jockey (Chinchaysuyo)',  'precio' => 7.00,  'orden' => 9],
            ['nombre' => 'Cond. El Jockey (Unión)',         'precio' => 8.00,  'orden' => 10],
            ['nombre' => 'La Victoria (pasando la vía)',    'precio' => 9.00,  'orden' => 11],
            ['nombre' => 'Urb. Santa Margarita',            'precio' => 8.00,  'orden' => 12],
            ['nombre' => 'PJ 9 de Octubre',                 'precio' => 8.00,  'orden' => 13],
            ['nombre' => 'Urb. Las Brisas',                 'precio' => 8.00,  'orden' => 14],
            ['nombre' => 'Urb. El Santuario',               'precio' => 9.00,  'orden' => 15],
            ['nombre' => 'Cond. Ntra. Sra. de la Paz',      'precio' => 10.00, 'orden' => 16],
            ['nombre' => 'Urb. Los Sauces',                 'precio' => 13.00, 'orden' => 17],
            ['nombre' => 'Urb. Los Nogales',                'precio' => 15.00, 'orden' => 18],
            ['nombre' => 'Urb. Los Álamos (La Victoria)',   'precio' => 10.00, 'orden' => 19],
            ['nombre' => 'Urb. Santa Rosa (La Victoria)',   'precio' => 10.00, 'orden' => 20],
            ['nombre' => 'Urb. La Purísima',                'precio' => 8.00,  'orden' => 21],
            ['nombre' => 'Av. Leguía (L. González - Balta)', 'precio' => 6.00,  'orden' => 22],
            ['nombre' => 'JLO (hasta antes de Dorado)',     'precio' => 7.00,  'orden' => 23],
            ['nombre' => 'Hasta México',                    'precio' => 8.00,  'orden' => 24],
            ['nombre' => 'Hasta Av. Chiclayo',              'precio' => 10.00, 'orden' => 25],
            ['nombre' => 'Alameda Grau',                    'precio' => 8.00,  'orden' => 26],
            ['nombre' => 'Alameda Pacasmayo',               'precio' => 8.00,  'orden' => 27],
            ['nombre' => 'Pinos de La Plata',               'precio' => 8.00,  'orden' => 28],
            ['nombre' => 'Urb. La Florida',                 'precio' => 7.00,  'orden' => 29],
            ['nombre' => 'Urb. Santa Ángela',               'precio' => 7.00,  'orden' => 30],

            // ── Afueras de Chiclayo ───────────────────────
            ['nombre' => 'Pimentel',                        'precio' => 18.00, 'orden' => 31],
            ['nombre' => 'Lambayeque',                      'precio' => 18.00, 'orden' => 32],
            ['nombre' => 'Santa Rosa',                       'precio' => 25.00, 'orden' => 33],
            ['nombre' => 'Ferreñafe',                        'precio' => 35.00, 'orden' => 34],
            ['nombre' => 'Picsi',                            'precio' => 15.00, 'orden' => 35],
            ['nombre' => 'Pomalca',                          'precio' => 15.00, 'orden' => 36],
            ['nombre' => 'Tumán',                            'precio' => 25.00, 'orden' => 37],
        ];

        foreach ($zonas as $zona) {
            DB::table('delivery_zones')->updateOrInsert(
                ['nombre' => $zona['nombre']], // condición para buscar si ya existe
                [
                    'precio'     => $zona['precio'],
                    'orden'      => $zona['orden'],
                    'activo'     => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
