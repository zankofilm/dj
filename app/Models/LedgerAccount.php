<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    protected $fillable = [
        'code', 'name', 'account_type', 'normal_side',
        'currency', 'is_system', 'is_active', 'metadata',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'ledger_account_id');
    }
}
