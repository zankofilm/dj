<?php
namespace App\Services\Payments;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class PaymentService {
 public function __construct(private readonly PaymentGatewayManager $gateways) {}
 public function begin(Order $order,int $userId,string $provider,string $key): Payment {
  if($order->user_id!==$userId || $order->status!=='pending_payment') throw new PaymentException('سفارش قابل پرداخت نیست.');
  if(strlen($key)<16 || strlen($key)>100) throw new PaymentException('کلید درخواست معتبر نیست.');
  $gateway=$this->gateways->driver($provider);
  $payment=DB::transaction(function() use($order,$userId,$provider,$key) {
   $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
   $existing=Payment::where('idempotency_key',$key)->lockForUpdate()->first();
   if($existing) {
    if($existing->order_id!==$locked->id || $existing->user_id!==$userId) throw new PaymentException('کلید درخواست تکراری است.');
    return $existing;
   }
   if(Payment::where('order_id',$locked->id)->whereIn('status',['initiating','pending','unknown'])->lockForUpdate()->exists())
    throw new PaymentException('برای سفارش پرداخت باز یا نامشخص وجود دارد.');
   $p=Payment::create(['public_id'=>(string)Str::uuid(),'order_id'=>$locked->id,'user_id'=>$userId,'provider_key'=>$provider,
    'status'=>'initiating','currency'=>$locked->currency,'amount_minor'=>$locked->total_minor,'idempotency_key'=>$key]);
   $p->attempts()->create(['attempt_number'=>1,'status'=>'initiating','started_at'=>now(),
    'request_fingerprint'=>hash('sha256',$locked->public_id.'|'.$locked->total_minor.'|'.$locked->currency)]);
   return $p;
  });
  if($payment->status!=='initiating' || $payment->provider_reference) return $payment;
  try {
   $result=$gateway->initiate($payment);
   if(empty($result['provider_reference']) || empty($result['checkout_url']) ||
      !filter_var($result['checkout_url'],FILTER_VALIDATE_URL) || parse_url($result['checkout_url'],PHP_URL_SCHEME)!=='https')
    throw new PaymentException('پاسخ درگاه معتبر نیست.');
   return DB::transaction(function() use($payment,$result) {
    $p=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
    if($p->status!=='initiating') return $p;
    $p->update(['status'=>'pending','provider_reference'=>$result['provider_reference'],'checkout_url'=>$result['checkout_url'],'expires_at'=>$result['expires_at']??null]);
    $p->attempts()->latest('id')->first()?->update(['status'=>'pending','provider_reference'=>$result['provider_reference'],'finished_at'=>now()]);
    return $p;
   });
  } catch(\Throwable $e) {
   $payment->update(['status'=>'unknown','failure_code'=>'initiation_uncertain','failure_message'=>mb_substr($e->getMessage(),0,500)]);
   throw new PaymentException('نتیجه ایجاد پرداخت نامشخص است؛ پیش از تلاش مجدد باید از درگاه استعلام شود.');
  }
 }
 public function verifyAndApply(Payment $payment): Payment {
  $v=$this->gateways->driver($payment->provider_key)->verifyTransaction($payment);
  if(($v['status']??'')==='pending') return $payment;
  if(($v['status']??'')!=='paid' || (int)($v['amount_minor']??-1)!==(int)$payment->amount_minor ||
     strtoupper((string)($v['currency']??''))!==strtoupper($payment->currency) || empty($v['provider_reference'])) {
   $payment->update(['status'=>'unknown','failure_code'=>'verification_mismatch']);
   throw new PaymentException('تأیید پرداخت با سفارش تطبیق ندارد؛ بررسی لازم است.');
  }
  return DB::transaction(function() use($payment,$v) {
   $p=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
   if($p->status==='paid') return $p;
   if(!in_array($p->status,['pending','unknown','initiating'],true)) throw new PaymentException('وضعیت پرداخت قابل تغییر نیست.');
   $p->update(['status'=>'paid','provider_reference'=>$v['provider_reference'],'paid_at'=>now(),'failure_code'=>null]);
   $order=Order::whereKey($p->order_id)->lockForUpdate()->firstOrFail();
   if($order->status==='pending_payment') {
    $order->update(['status'=>'paid']);
    $order->sellerOrders()->where('status','pending_payment')->update(['status'=>'paid']);
   }
   AuditLog::create(['action'=>'payment.verified_paid','subject_type'=>Payment::class,'subject_id'=>(string)$p->id,
    'after_data'=>['provider_key'=>$p->provider_key,'amount_minor'=>$p->amount_minor,'currency'=>$p->currency],
    'created_at'=>now()]);
   return $p;
  },3);
 }
}
