<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Payment extends Model {
 protected $fillable=['public_id','order_id','user_id','provider_key','status','currency','amount_minor','idempotency_key','provider_reference','checkout_url','expires_at','paid_at','failure_code','failure_message','metadata'];
 protected function casts(): array { return ['metadata'=>'array','expires_at'=>'datetime','paid_at'=>'datetime']; }
 public function order(): BelongsTo { return $this->belongsTo(Order::class); }
 public function user(): BelongsTo { return $this->belongsTo(User::class); }
 public function attempts(): HasMany { return $this->hasMany(PaymentAttempt::class); }
}
