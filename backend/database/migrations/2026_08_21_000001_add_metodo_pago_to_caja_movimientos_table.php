<?php
// database/migrations/2026_08_21_000001_add_metodo_pago_to_caja_movimientos_table.php
//
// Antes, solo entraban a caja los pedidos pagados en efectivo (por
// diseño, para que el cuadre físico tuviera sentido). Ahora TODAS las
// ventas entran y se ven, pero este campo permite que el cuadre siga
// sumando solo lo que de verdad es efectivo — Yape/tarjeta/anticipado
// se ven en la lista, pero no cuentan para "lo que debe haber en caja".

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->string('metodo_pago')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};
