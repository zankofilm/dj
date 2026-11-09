<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function __invoke(string $provider, Request $request, PaymentGatewayManager $gateways, PaymentService $service)
    {
        $rawBody = $request->getContent();
        $event = $gateways->driver($provider)->verifyWebhook($request->headers->all(), $rawBody);

        if (empty($event['valid']) || empty($event['event_id']) || empty($event['payment_reference'])
            || !is_scalar($event['event_id']) || !is_scalar($event['payment_reference'])) {
            abort(401, 'اعلان پرداخت معتبر نیست.');
        }

        $eventId = (string) $event['event_id'];
        $paymentReference = (string) $event['payment_reference'];
        if ($eventId === '' || strlen($eventId) > 190 || $paymentReference === '' || strlen($paymentReference) > 190) {
            abort(422, 'شناسه رویداد یا مرجع پرداخت معتبر نیست.');
        }

        $payloadHash = hash('sha256', $rawBody);
        try {
            $record = PaymentWebhookEvent::firstOrCreate(
                ['provider_key'=>$provider, 'provider_event_id'=>$eventId],
                ['event_type'=>(string)($event['event_type'] ?? 'unknown'),
                 'payment_reference'=>$paymentReference, 'payload_hash'=>$payloadHash,
                 'signature_valid'=>true, 'processing_status'=>'received', 'received_at'=>now()]
            );
        } catch (QueryException $exception) {
            $record = PaymentWebhookEvent::where('provider_key',$provider)
                ->where('provider_event_id',$eventId)->first();
            if (!$record) throw $exception;
        }

        if (!hash_equals((string)$record->payload_hash,$payloadHash)
            || !hash_equals((string)$record->payment_reference,$paymentReference)) {
            abort(409, 'شناسه رویداد با محتوای قبلی مطابقت ندارد.');
        }

        $claim = DB::transaction(function () use ($record) {
            $locked = PaymentWebhookEvent::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if ($locked->processing_status === 'processed') return 'processed';
            if ($locked->processing_status === 'processing') return 'processing';
            $locked->update(['processing_status'=>'processing','processing_started_at'=>now(),'failure_message'=>null]);
            return 'claimed';
        }, 3);

        if ($claim === 'processed') return response('',200);
        if ($claim === 'processing') return response('',202)->header('Retry-After','5');

        try {
            $payment = Payment::where('provider_key',$provider)
                ->where('provider_reference',$paymentReference)->firstOrFail();
            $service->verifyAndApply($payment);
            $record->update(['processing_status'=>'processed','processed_at'=>now(),'failure_message'=>null]);
        } catch (\Throwable $exception) {
            report($exception);
            $record->update([
                'processing_status'=>'needs_review',
                'failure_message'=>'پردازش رویداد ناموفق بود؛ بررسی و تطبیق دستی لازم است.'
            ]);
        }
        return response('',200);
    }
}
