<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentAttempt extends Model {
 protected $fillable=['payment_id','attempt_number','status','provider_reference','request_fingerprint','response_code','failure_code','failure_message','started_at','finished_at'];
 protected function casts(): array { return ['started_at'=>'datetime','finished_at'=>'datetime']; }
 public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
