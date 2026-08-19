<?php
// database/migrations/2026_08_17_000001_add_control_fields_to_caja_table.php
//
// Mejoras críticas de control para que el cierre de caja sea real:
// - monto_contado/diferencia/motivo_diferencia: el cuadre de verdad
//   (contar el efectivo físico y compararlo contra lo que el sistema
//   esperaba, en vez de que el sistema se crea a sí mismo).
// - cerrado_por: quién cerró, no solo quién abrió.
// - motivo_reapertura: rastro de por qué se reabrió una caja ya cerrada.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('monto_contado', 10, 2)->nullable()->after('monto_cierre');
            $table->decimal('diferencia', 10, 2)->nullable()->after('monto_contado');
            $table->string('motivo_diferencia')->nullable()->after('diferencia');
            $table->foreignId('cerrado_por')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('motivo_reapertura')->nullable()->after('cerrada_at');
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->boolean('anulado')->default(false)->after('description');
            $table->string('motivo_anulacion')->nullable()->after('anulado');
            $table->timestamp('anulado_at')->nullable()->after('motivo_anulacion');
            $table->foreignId('anulado_por')->nullable()->after('anulado_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cerrado_por');
            $table->dropColumn(['monto_contado', 'diferencia', 'motivo_diferencia', 'motivo_reapertura']);
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('anulado_por');
            $table->dropColumn(['anulado', 'motivo_anulacion', 'anulado_at']);
        });
    }
};
