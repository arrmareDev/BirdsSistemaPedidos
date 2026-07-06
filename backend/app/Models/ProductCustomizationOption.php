<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCustomizationOption extends Model
{
    protected $table = 'product_customization_options';

    protected $fillable = [
        'section_id',
        'name',
        'price_modifier',
        'sort_order',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'sort_order'      => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductCustomizationSection::class, 'section_id');
    }
}
