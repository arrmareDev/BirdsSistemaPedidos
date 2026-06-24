<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductExtra extends Model
{
    protected $table = 'product_extras';

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
