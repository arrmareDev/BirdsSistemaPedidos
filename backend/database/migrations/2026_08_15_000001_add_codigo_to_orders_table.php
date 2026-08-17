<?php
// database/migrations/2026_08_15_000001_add_codigo_to_orders_table.php
//
// Identificador único del pedido de cara al cliente/staff — un número
// aleatorio de 5 cifras (10000-99999), en vez del id incremental de
// la base de datos. El id interno se mantiene intacto para las
// relaciones, este código es solo para mostrar y para identificar el
// pedido ante DeliveryCentral.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('codigo')->nullable()->unique()->after('id');
        });

        // Pedidos que ya existían antes de este cambio también reciben
        // su código, para no dejar filas viejas sin uno.
        $usados = [];
        DB::table('orders')->whereNull('codigo')->orderBy('id')->each(function ($order) use (&$usados) {
            do {
                $codigo = random_int(10000, 99999);
            } while (isset($usados[$codigo]) || DB::table('orders')->where('codigo', $codigo)->exists());

            $usados[$codigo] = true;
            DB::table('orders')->where('id', $order->id)->update(['codigo' => $codigo]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
