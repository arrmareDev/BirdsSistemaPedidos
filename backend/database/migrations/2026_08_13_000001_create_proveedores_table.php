<?php
// database/migrations/2026_08_13_000001_create_proveedores_table.php
//
// Directorio interno de proveedores aliados, solo dentro del panel admin.
// Gestionado exclusivamente por el rol "sistema" — el negocio (admin)
// no lo administra, es una curaduría del equipo detrás del sistema.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable();
            $table->string('url_externa');
            $table->string('descuento_texto')->nullable();
            $table->unsignedInteger('clics')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
