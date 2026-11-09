<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SellerSettlement extends Model
{
    protected $fillable = [
        'public_id', 'seller_id', 'seller_order_id', 'currency',
        'gross_minor', 'commission_minor', 'adjustments_minor',
        'net_payable_minor', 'status', 'eligible_at', 'approved_at',
        'paid_at', 'ledger_event_key', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'eligible_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function payouts(): BelongsToMany
    {
        return $this->belongsToMany(
            SellerPayout::class,
            'seller_payout_settlement',
            'seller_settlement_id',
            'seller_payout_id'
        )->withPivot('allocated_minor')->withTimestamps();
    }
}
