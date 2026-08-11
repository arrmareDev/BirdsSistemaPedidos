<?php
// database/migrations/2026_08_10_000001_add_must_change_password_to_users.php
//
// Marca cuentas que deben cambiar su contraseña en el próximo login —
// se activa cuando el rol "sistema" resetea la clave de emergencia de
// otro usuario, para que esa clave temporal no quede válida para siempre.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
