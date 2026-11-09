<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'seller_order_id', 'seller_id', 'product_id',
        'variant_id', 'product_title_snapshot', 'sku_snapshot',
        'quantity', 'unit_price_minor', 'line_total_minor',
        'commercial_snapshot',
    ];

    protected function casts(): array
    {
        return ['commercial_snapshot' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }
}
