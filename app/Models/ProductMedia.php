<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id', 'storage_path', 'alt_translations',
        'mime_type', 'file_size', 'sort_order', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'alt_translations' => 'array',
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
