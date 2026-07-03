<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'emoji',
        'ocasion',
        'color',
        'tamano',
        'stock',
        'controla_stock',
        'price',
        'image',
        'available',
        'popular',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'available'       => 'boolean',
        'popular'         => 'boolean',
        'controla_stock'  => 'boolean',
        'stock'           => 'integer',
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

    public function extras(): HasMany
    {
        return $this->hasMany(ProductExtra::class)
            ->orderBy('sort_order');
    }

    public function reducirStock(int $cantidad): void
    {
        if ($this->controla_stock && $this->stock >= $cantidad) {
            $this->decrement('stock', $cantidad);
        }
    }

    public function tieneStock(int $cantidad = 1): bool
    {
        if (!$this->controla_stock) return true;
        return $this->stock >= $cantidad;
    }

    public function getImageUrlAttribute(): string|null
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
}
