<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_phone')->nullable();

            // ── Florería: solo recoger o delivery, sin local/mesa ──
            $table->enum('type', ['recoger', 'delivery'])->default('recoger');

            $table->enum('status', [
                'nuevo',
                'confirmado',
                'preparando',
                'listo',
                'en_camino',
                'entregado',
                'cancelado',
            ])->default('nuevo');

            // ── Delivery ──────────────────────────────────────────
            $table->foreignId('delivery_zone_id')
                ->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('reference')->nullable();
            $table->string('district')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // ── Pago ──────────────────────────────────────────────
            $table->enum('metodo_pago', [
                'anticipado',
                'contraentrega_efectivo',
                'contraentrega_yape',
            ])->nullable();

            // ── Notas ─────────────────────────────────────────────
            $table->text('note')->nullable();

            // ── Florería ──────────────────────────────────────────
            $table->string('mensaje_tarjeta')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->time('hora_entrega')->nullable();
            $table->boolean('entrega_programada')->default(false);

            // ── Totales ───────────────────────────────────────────
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total',    10, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')
                ->nullable()->constrained()->nullOnDelete();
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal',   10, 2);
            $table->json('customization')->nullable();
            $table->json('extras')->nullable();
            $table->text('custom_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
