<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('monto_pedido',   10, 2);
            $table->decimal('monto_comision', 10, 2);
            $table->date('fecha');
            $table->boolean('cobrado')->default(false);
            $table->timestamp('cobrado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        DB::table('configuracion_sistema')->insert([
            [
                'clave'       => 'comision_por_pedido',
                'valor'       => '0.30',
                'descripcion' => 'Comisión por cada pedido entregado (S/)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones');
        Schema::dropIfExists('configuracion_sistema');
    }
};
