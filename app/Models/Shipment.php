<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'public_id', 'seller_order_id', 'provider_key', 'status',
        'tracking_number', 'label_reference', 'shipping_minor', 'currency',
        'recipient_snapshot', 'provider_metadata', 'shipped_at',
        'delivered_at', 'cancelled_at', 'failure_code', 'failure_message',
    ];

    protected function casts(): array
    {
        return [
            'recipient_snapshot' => 'array',
            'provider_metadata' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderBy('occurred_at');
    }
}
