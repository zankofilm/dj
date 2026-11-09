<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayout extends Model
{
    protected $fillable = [
        'public_id', 'seller_id', 'payout_destination_id', 'currency',
        'amount_minor', 'status', 'provider_key', 'provider_reference',
        'idempotency_key', 'requested_at', 'approved_at', 'submitted_at',
        'paid_at', 'failure_code', 'failure_message', 'approved_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function settlements()
    {
        return $this->belongsToMany(SellerSettlement::class, 'seller_payout_settlement')
            ->withPivot('allocated_minor')->withTimestamps();
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(SellerPayoutDestination::class, 'payout_destination_id');
    }
}
