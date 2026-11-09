<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageBanner extends Model
{
    protected $fillable = [
        'image_path', 'translations', 'target_url', 'sort_order',
        'is_active', 'starts_at', 'ends_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isVisibleNow(): bool
    {
        $now = now();

        return $this->is_active
            && (!$this->starts_at || $this->starts_at->lte($now))
            && (!$this->ends_at || $this->ends_at->gte($now));
    }
}
