<?php
// database/migrations/2026_08_19_000001_add_descuento_to_products_table.php
//
// Promociones por producto — Opción A del diseño acordado: campos
// simples en el propio producto (no una tabla de campañas aparte).
// El precio final NUNCA se guarda — se calcula al vuelo en el
// modelo a partir de price + estos campos, para que nunca se
// desincronice si el precio base cambia después.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 'porcentaje' | 'monto_fijo' — string, no enum nativo, mismo
            // patrón que el resto del proyecto usa para este tipo de campo
            // (validado en la app, no a nivel de base de datos).
            $table->string('descuento_tipo')->nullable()->after('price');
            $table->decimal('descuento_valor', 10, 2)->nullable()->after('descuento_tipo');
            $table->date('descuento_desde')->nullable()->after('descuento_valor');
            $table->date('descuento_hasta')->nullable()->after('descuento_desde');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['descuento_tipo', 'descuento_valor', 'descuento_desde', 'descuento_hasta']);
        });
    }
};
