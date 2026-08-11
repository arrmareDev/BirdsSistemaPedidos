<?php
// database/migrations/2026_08_10_000005_create_seccion_tipos_table.php
//
// Reemplaza los 6 "tipos de sección" fijos (Envoltura/Lazo/Follaje/...)
// por una lista real que el admin puede crear, editar, reordenar y
// eliminar — no solo renombrar 6 casillas fijas.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seccion_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 40);
            $table->string('icono', 40)->default('sparkles');
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Semilla con los 6 tipos que ya existían, respetando cualquier
        // nombre que el admin ya hubiera configurado en Ajustes antes
        // de este cambio (si nunca los tocó, usa los nombres originales).
        $defaults = [
            'envoltura'    => ['nombre' => 'Envoltura', 'icono' => 'gift'],
            'lazo'         => ['nombre' => 'Lazo / Cinta', 'icono' => 'ribbon'],
            'follaje'      => ['nombre' => 'Follaje', 'icono' => 'leaf'],
            'dedicatoria'  => ['nombre' => 'Dedicatoria', 'icono' => 'pen-line'],
            'presentacion' => ['nombre' => 'Presentación', 'icono' => 'package'],
            'complemento'  => ['nombre' => 'Complemento', 'icono' => 'sparkles'],
        ];

        $orden = 0;
        foreach ($defaults as $key => $info) {
            $labelGuardado = DB::table('configuracion_sistema')
                ->where('clave', "seccion_{$key}_label")->value('valor');
            $activoGuardado = DB::table('configuracion_sistema')
                ->where('clave', "seccion_{$key}_activo")->value('valor');

            DB::table('seccion_tipos')->insert([
                'nombre'     => $labelGuardado ?: $info['nombre'],
                'icono'      => $info['icono'],
                'activo'     => $activoGuardado === null ? true : (bool) (int) $activoGuardado,
                'sort_order' => $orden++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // La config vieja de 6 casillas fijas ya no aplica — la tabla
        // de arriba es ahora la única fuente de verdad.
        DB::table('configuracion_sistema')->where('clave', 'like', 'seccion_%')->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('seccion_tipos');
    }
};
