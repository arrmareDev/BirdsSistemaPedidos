<?php
// database/migrations/2026_08_08_000003_drop_business_line_from_extras.php
//
// Los extras ya no se agrupan por línea de negocio — su relevancia se
// define directamente por a qué productos están vinculados (extra_product).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            $table->dropColumn('business_line');
        });
    }

    public function down(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            $table->string('business_line')->default('cafeteria')->after('price');
        });
    }
};
