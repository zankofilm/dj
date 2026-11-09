<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsVerificationCode extends Model
{
    protected $fillable = ['phone', 'purpose', 'code_hash', 'expires_at', 'sent_at', 'attempts', 'consumed_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }
}
