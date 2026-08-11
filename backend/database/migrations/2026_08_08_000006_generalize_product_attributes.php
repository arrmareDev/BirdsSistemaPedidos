<?php
// database/migrations/2026_08_08_000006_generalize_product_attributes.php
//
// Los campos ocasion/color/tamano estaban pensados solo para florería.
// Pasan a ser 3 atributos genéricos (atributo_1/2/3) cuya ETIQUETA se
// define desde el panel (ej: "Ocasión" para una florería, "Material"
// para una ferretería) — así cualquier negocio los puede reutilizar
// sin que un desarrollador toque el código.
//
// Los datos existentes se preservan: ocasion→atributo_1, color→atributo_2,
// tamano→atributo_3 (mismo orden en que ya se usaban).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('ocasion', 'atributo_1');
            $table->renameColumn('color', 'atributo_2');
            $table->renameColumn('tamano', 'atributo_3');
        });

        // Etiquetas y visibilidad configurables — con los nombres de Birds
        // como valor por defecto, para no romper lo que ya existe.
        $defaults = [
            'atributo_1_label'  => 'Ocasión',
            'atributo_1_activo' => '1',
            'atributo_2_label'  => 'Color',
            'atributo_2_activo' => '1',
            'atributo_3_label'  => 'Tamaño',
            'atributo_3_activo' => '1',
        ];

        foreach ($defaults as $clave => $valor) {
            DB::table('configuracion_sistema')->insertOrIgnore([
                'clave'       => $clave,
                'valor'       => $valor,
                'descripcion' => 'Atributo de producto configurable',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('atributo_1', 'ocasion');
            $table->renameColumn('atributo_2', 'color');
            $table->renameColumn('atributo_3', 'tamano');
        });

        DB::table('configuracion_sistema')->whereIn('clave', [
            'atributo_1_label',
            'atributo_1_activo',
            'atributo_2_label',
            'atributo_2_activo',
            'atributo_3_label',
            'atributo_3_activo',
        ])->delete();
    }
};
