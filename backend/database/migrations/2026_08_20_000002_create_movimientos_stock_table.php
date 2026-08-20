<?php
// database/migrations/2026_08_20_000002_create_movimientos_stock_table.php
//
// Historial de TODO cambio de stock — el número en products.stock ya
// se movía antes (venta, edición de pedido), pero en silencio, sin
// ningún rastro. Esto no reemplaza esos cambios, los explica.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // 'venta' | 'cancelacion' | 'edicion_pedido' | 'reposicion' | 'ajuste'
            $table->string('tipo');
            // Con signo — negativo resta, positivo suma. stock_resultante
            // guarda la foto de después del cambio, para poder reconstruir
            // el historial completo sin tener que recalcular nada.
            $table->integer('cantidad');
            $table->integer('stock_resultante');
            $table->string('motivo')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};
