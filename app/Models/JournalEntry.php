<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'public_id', 'event_key', 'event_type', 'source_type', 'source_id',
        'currency', 'description', 'status', 'posted_at', 'reversal_of_id',
        'metadata', 'created_by',
    ];

    protected function casts(): array
    {
        return ['posted_at' => 'datetime', 'metadata' => 'array'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_number');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }
}
