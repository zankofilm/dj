<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $fillable = ['slug', 'translations', 'seo', 'content_blocks', 'status', 'created_by', 'updated_by', 'published_at'];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'seo' => 'array',
            'content_blocks' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
