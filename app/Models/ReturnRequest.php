<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    protected $fillable = [
        'public_id', 'order_item_id', 'seller_order_id', 'customer_user_id',
        'quantity', 'reason_code', 'customer_note', 'status',
        'resolution_type', 'requested_refund_minor', 'approved_refund_minor',
        'currency', 'decision_note', 'reviewed_by', 'reviewed_at',
        'received_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'received_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReturnEvent::class)->orderBy('created_at');
    }
}
