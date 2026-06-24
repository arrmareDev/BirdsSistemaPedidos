<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'unit_price',
        'subtotal',
        'customization',
        'extras',
        'custom_summary',
    ];

    protected $casts = [
        'unit_price'    => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'customization' => 'array',
        'extras'        => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
