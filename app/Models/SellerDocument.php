<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerDocument extends Model
{
    protected $fillable = [
        'seller_id', 'document_type', 'storage_path', 'original_name',
        'mime_type', 'file_size', 'status', 'reviewed_by', 'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
