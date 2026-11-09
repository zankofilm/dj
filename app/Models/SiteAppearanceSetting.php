<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteAppearanceSetting extends Model
{
    protected $fillable = ['setting_key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function allValues(): array
    {
        return static::query()->get()->mapWithKeys(fn ($row) => [$row->setting_key => $row->value])->all();
    }
}
