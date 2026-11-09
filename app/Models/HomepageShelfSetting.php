<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageShelfSetting extends Model
{
    protected $fillable = ['shelf_key', 'translations', 'is_active', 'item_limit', 'sort_order', 'motion_speed'];

    protected function casts(): array
    {
        return ['translations' => 'array', 'is_active' => 'boolean'];
    }

    public function titleFor(string $locale): string
    {
        return (string) data_get($this->translations, $locale.'.title',
            data_get($this->translations, 'fa.title', $this->shelf_key));
    }

    public function eyebrowFor(string $locale): string
    {
        return (string) data_get($this->translations, $locale.'.eyebrow',
            data_get($this->translations, 'fa.eyebrow', ''));
    }
}
