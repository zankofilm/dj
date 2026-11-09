<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentWebhookEvent extends Model {
    protected $fillable = [
        'provider_key','provider_event_id','event_type','payment_reference',
        'payload_hash','signature_valid','processing_status','received_at',
        'processing_started_at','processed_at','failure_message'
    ];
    protected function casts(): array {
        return ['signature_valid'=>'boolean','received_at'=>'datetime',
            'processing_started_at'=>'datetime','processed_at'=>'datetime'];
    }
}
