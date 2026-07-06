<?php

namespace App\Models;

use App\Enums\BusinessLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extra extends Model
{
    protected $fillable = [
        'name',
        'price',
        'business_line',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'business_line' => BusinessLine::class,
            'price'         => 'decimal:2',
            'active'        => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'extra_product');
    }

    public function scopeOfBusinessLine($query, BusinessLine $line)
    {
        return $query->where('business_line', $line->value);
    }
}
