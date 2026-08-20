<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'icon',
        'stock',
        'stock_minimo',
        'controla_stock',
        'price',
        'image',
        'available',
        'popular',
        'descuento_tipo',
        'descuento_valor',
        'descuento_desde',
        'descuento_hasta',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'available'       => 'boolean',
        'popular'         => 'boolean',
        'controla_stock'  => 'boolean',
        'stock'           => 'integer',
        'stock_minimo'    => 'integer',
        'descuento_valor' => 'decimal:2',
        'descuento_desde' => 'date',
        'descuento_hasta' => 'date',
    ];

    protected $appends = ['image_url'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = self::generateUniqueSlug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = self::generateUniqueSlug($product->name);
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function customizationSections(): HasMany
    {
        return $this->hasMany(ProductCustomizationSection::class)
            ->orderBy('sort_order');
    }

    // Galería de fotos generales del producto (distintos ángulos, etc.)
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    // ── Extras únicos por producto (relación 1 a 1) ────────────
    public function extras(): HasMany
    {
        return $this->hasMany(ProductExtra::class)
            ->orderBy('sort_order');
    }

    // ── Extras compartidos (un mismo extra puede aplicar a varios productos) ──
    public function extrasCompartidos(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'extra_product')
            ->where('active', true)
            ->orderBy('sort_order');
    }

    public function reducirStock(
        int $cantidad,
        string $tipo = 'venta',
        ?int $orderId = null,
        ?string $motivo = null,
    ): void {
        if (!$this->controla_stock || $this->stock < $cantidad) return;

        $this->decrement('stock', $cantidad);
        $this->registrarMovimientoStock(-$cantidad, $tipo, $orderId, $motivo);
    }

    public function restaurarStock(
        int $cantidad,
        string $tipo = 'ajuste',
        ?int $orderId = null,
        ?string $motivo = null,
    ): void {
        if (!$this->controla_stock) return;

        $this->increment('stock', $cantidad);
        $this->registrarMovimientoStock($cantidad, $tipo, $orderId, $motivo);
    }

    private function registrarMovimientoStock(
        int $delta,
        string $tipo,
        ?int $orderId,
        ?string $motivo,
    ): void {
        $this->movimientosStock()->create([
            'tipo'             => $tipo,
            'cantidad'         => $delta,
            'stock_resultante' => $this->fresh()->stock,
            'order_id'         => $orderId,
            'motivo'           => $motivo,
            'user_id'          => auth()->id(),
        ]);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        if (!$this->controla_stock) return true;
        return $this->stock >= $cantidad;
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->controla_stock
            && $this->stock_minimo !== null
            && $this->stock <= $this->stock_minimo;
    }

    public function getImageUrlAttribute(): string|null
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }

    // ── Promociones/descuentos ───────────────────────────────
    // El precio final NUNCA se guarda en la base — se calcula acá,
    // así nunca queda desincronizado si el precio base cambia.

    public function tieneDescuentoActivo(): bool
    {
        if (!$this->descuento_tipo || $this->descuento_valor === null) {
            return false;
        }

        $hoy = now()->startOfDay();

        if ($this->descuento_desde && $hoy->lt($this->descuento_desde)) {
            return false;
        }

        if ($this->descuento_hasta && $hoy->gt($this->descuento_hasta)) {
            return false;
        }

        return true;
    }

    public function getPrecioFinalAttribute(): float
    {
        if (!$this->tieneDescuentoActivo()) {
            return (float) $this->price;
        }

        $precio = (float) $this->price;
        $valor  = (float) $this->descuento_valor;

        $final = $this->descuento_tipo === 'porcentaje'
            ? $precio - ($precio * $valor / 100)
            : $precio - $valor;

        // Nunca queda en negativo, aunque alguien ponga un monto fijo
        // más grande que el precio por error.
        return max(0, round($final, 2));
    }

    // Porcentaje para el badge "-X%" — se calcula también cuando el
    // descuento es de monto fijo, para poder mostrar el mismo tipo
    // de badge sin importar cómo se configuró.
    public function getDescuentoPorcentajeAttribute(): ?int
    {
        if (!$this->tieneDescuentoActivo() || (float) $this->price <= 0) {
            return null;
        }

        if ($this->descuento_tipo === 'porcentaje') {
            return (int) round((float) $this->descuento_valor);
        }

        $ahorro = (float) $this->price - $this->precio_final;
        return (int) round(($ahorro / (float) $this->price) * 100);
    }
}
