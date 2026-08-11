<?php
// database/migrations/2026_08_10_000004_remove_product_attributes.php
//
// Elimina por completo el sistema de atributos configurables
// (Ocasión/Color/Tamaño) — no como un toggle, sino quitando las
// columnas y su configuración. Decisión del negocio: no se van a
// usar, ni apagados ni encendidos.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['atributo_1', 'atributo_2', 'atributo_3']);
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

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('atributo_1', 60)->nullable();
            $table->string('atributo_2', 40)->nullable();
            $table->string('atributo_3', 40)->nullable();
        });
    }
};
