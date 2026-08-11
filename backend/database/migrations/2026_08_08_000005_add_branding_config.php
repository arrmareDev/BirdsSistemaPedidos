<?php
// database/migrations/2026_08_08_000005_add_branding_config.php
//
// Agrega las claves de marca (nombre del negocio, colores, logo, contacto)
// a la tabla de configuración clave-valor que ya existía para la comisión.
// Así cualquier negocio que use este sistema puede personalizar su marca
// desde el panel de administración, sin tocar código.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'nombre_negocio'      => 'Birds',
            'logo_url'            => '/images/logobirds.png',
            'color_primario'      => '#C41E1E',
            'color_primario_dark' => '#9B1717',
            'telefono'            => '984199340',
            'whatsapp'            => '984199340',
            'direccion'           => 'Chiclayo, Torres Paz N° 361',
        ];

        $descripciones = [
            'nombre_negocio'      => 'Nombre del negocio, se muestra en el catálogo y el panel',
            'logo_url'            => 'Ruta o URL del logo',
            'color_primario'      => 'Color principal de marca (botones, acentos) en formato hex',
            'color_primario_dark' => 'Variante oscura del color principal (hover, sombras)',
            'telefono'            => 'Teléfono de contacto que se muestra a los clientes',
            'whatsapp'            => 'Número de WhatsApp para pedidos y soporte (con código de país, sin +)',
            'direccion'           => 'Dirección del local',
        ];

        foreach ($defaults as $clave => $valor) {
            DB::table('configuracion_sistema')->insertOrIgnore([
                'clave'       => $clave,
                'valor'       => $valor,
                'descripcion' => $descripciones[$clave],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('configuracion_sistema')->whereIn('clave', [
            'nombre_negocio',
            'logo_url',
            'color_primario',
            'color_primario_dark',
            'telefono',
            'whatsapp',
            'direccion',
        ])->delete();
    }
};
