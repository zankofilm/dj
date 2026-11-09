<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'title', 'option_values',
        'price_minor', 'compare_at_price_minor', 'currency',
        'track_inventory', 'status',
    ];

    protected function casts(): array
    {
        return [
            'option_values' => 'array',
            'track_inventory' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryBalance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InventoryBalance::class, 'variant_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }
}
