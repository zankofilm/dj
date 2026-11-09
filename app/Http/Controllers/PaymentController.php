<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
class PaymentController extends Controller {
 public function start(Request $request,string $publicId,PaymentService $service) {
  $order=Order::where('public_id',$publicId)->where('user_id',$request->user()->id)->firstOrFail();
  $d=$request->validate(['provider'=>['required','string','max:80'],'idempotency_key'=>['required','string','min:16','max:100']]);
  try { $p=$service->begin($order,$request->user()->id,$d['provider'],$d['idempotency_key']); }
  catch(PaymentException $e) { return back()->withErrors(['payment'=>$e->getMessage()]); }
  if(!$p->checkout_url) return back()->withErrors(['payment'=>'درگاه آماده نیست یا وضعیت نیازمند بررسی است.']);
  return redirect()->away($p->checkout_url);
 }
}
