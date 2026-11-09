<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'public_id', 'owner_user_id', 'display_name', 'legal_name',
        'registration_number', 'status', 'country_code', 'contact_email',
        'contact_phone', 'reviewed_by', 'reviewed_at', 'rejection_reason',
        'profile_data',
    ];

    protected function casts(): array
    {
        return [
            'profile_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(SellerSettlement::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(SellerPayout::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SellerDocument::class);
    }
}
