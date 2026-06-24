<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories primero — products depende de ella
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 2. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('emoji')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            // ── Florería ──────────────────────────────────────────
            $table->string('ocasion')->nullable();
            $table->string('color')->nullable();
            $table->string('tamano')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('controla_stock')->default(true);
            $table->boolean('popular')->default(false);
            $table->boolean('available')->default(true);
            $table->timestamps();
        });

        // 3. Secciones de personalización
        Schema::create('product_customization_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()->cascadeOnDelete();
            $table->string('seccion');
            $table->string('label');
            $table->boolean('required')->default(false);
            $table->boolean('multiple')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Opciones de cada sección
        Schema::create('product_customization_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')
                ->constrained('product_customization_sections')
                ->cascadeOnDelete();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Extras con precio
        Schema::create('product_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_extras');
        Schema::dropIfExists('product_customization_options');
        Schema::dropIfExists('product_customization_sections');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
