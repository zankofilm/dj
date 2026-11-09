<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $fillable = ['variant_id', 'on_hand', 'reserved', 'low_stock_threshold', 'version'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getAvailableAttribute(): int
    {
        return max(0, (int) $this->on_hand - (int) $this->reserved);
    }
}
