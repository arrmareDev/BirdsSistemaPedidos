<?php
// database/migrations/2026_08_10_000002_add_image_to_customization_options.php
//
// Cada opción seleccionable de una sección de personalización (ej: "Rojo"
// dentro de una sección "Color") puede tener su propia foto — así el
// cliente ve realmente cómo se ve cada variante antes de elegirla.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_customization_options', function (Blueprint $table) {
            $table->string('image')->nullable()->after('price_modifier');
        });
    }

    public function down(): void
    {
        Schema::table('product_customization_options', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
