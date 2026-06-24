<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCustomizationSection extends Model
{
    protected $table = 'product_customization_sections';

    protected $fillable = [
        'product_id',
        'seccion',
        'label',
        'required',
        'multiple',
        'sort_order',
    ];

    protected $casts = [
        'required'   => 'boolean',
        'multiple'   => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductCustomizationOption::class, 'section_id')
            ->orderBy('sort_order');
    }
}
