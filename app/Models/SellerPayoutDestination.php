<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayoutDestination extends Model
{
    protected $fillable = [
        'seller_id', 'country_code', 'currency', 'bank_name',
        'account_holder_name', 'account_identifier_encrypted',
        'verification_status', 'is_default', 'verified_at',
        'created_by', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'account_identifier_encrypted' => 'encrypted',
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
