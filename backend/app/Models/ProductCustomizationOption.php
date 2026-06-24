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
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductCustomizationSection::class, 'section_id');
    }
}
