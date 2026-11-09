<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerOrder extends Model
{
    protected $fillable = [
        'public_id', 'order_id', 'seller_id', 'status', 'currency',
        'subtotal_minor', 'shipping_minor', 'discount_minor', 'total_minor',
        'fulfillment_snapshot',
    ];

    protected function casts(): array
    {
        return ['fulfillment_snapshot' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
