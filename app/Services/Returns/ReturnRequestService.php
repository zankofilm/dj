<?php

namespace App\Services\Returns;

use App\Models\OrderItem;
use App\Models\ReturnEvent;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnRequestService
{
    public function request(int $customerId, int $orderItemId, int $quantity, string $reason, ?string $note = null): ReturnRequest
    {
        return DB::transaction(function () use ($customerId, $orderItemId, $quantity, $reason, $note) {
            $item = OrderItem::with('order', 'sellerOrder')
                ->whereKey($orderItemId)->lockForUpdate()->firstOrFail();

            if ($item->order->user_id !== $customerId) {
                throw new ReturnException('این قلم سفارش متعلق به شما نیست.');
            }
            if ($quantity < 1 || $quantity > $item->quantity) {
                throw new ReturnException('تعداد مرجوعی معتبر نیست.');
            }
            if (!$item->sellerOrder || !in_array($item->sellerOrder->status, ['delivered', 'completed'], true)) {
                throw new ReturnException('درخواست مرجوعی پس از تحویل قابل ثبت است.');
            }

            $alreadyRequested = (int) ReturnRequest::where('order_item_id', $item->id)
                ->whereNotIn('status', ['rejected', 'cancelled', 'closed'])
                ->sum('quantity');

            if ($alreadyRequested + $quantity > $item->quantity) {
                throw new ReturnException('برای این کالا قبلاً درخواست مرجوعی ثبت شده است.');
            }

            $amount = (int) $item->unit_price_minor * $quantity;
            $return = ReturnRequest::create([
                'public_id' => (string) Str::uuid(),
                'order_item_id' => $item->id,
                'seller_order_id' => $item->seller_order_id,
                'customer_user_id' => $customerId,
                'quantity' => $quantity,
                'reason_code' => $reason,
                'customer_note' => $note,
                'status' => 'requested',
                'requested_refund_minor' => $amount,
                'currency' => $item->sellerOrder->currency,
            ]);

            ReturnEvent::create([
                'return_request_id' => $return->id,
                'actor_user_id' => $customerId,
                'event_type' => 'return_requested',
                'to_status' => 'requested',
                'note' => $note,
            ]);

            return $return;
        }, 3);
    }

    public function decide(ReturnRequest $return, int $reviewerId, string $decision, ?int $approvedRefundMinor, ?string $note = null): ReturnRequest
    {
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new ReturnException('تصمیم مرجوعی معتبر نیست.');
        }

        return DB::transaction(function () use ($return, $reviewerId, $decision, $approvedRefundMinor, $note) {
            $locked = ReturnRequest::whereKey($return->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'requested') {
                throw new ReturnException('این درخواست قبلاً بررسی شده است.');
            }

            if ($decision === 'approved') {
                if ($approvedRefundMinor === null || $approvedRefundMinor < 0
                    || $approvedRefundMinor > $locked->requested_refund_minor) {
                    throw new ReturnException('مبلغ بازپرداخت تأییدشده معتبر نیست.');
                }
                $locked->approved_refund_minor = $approvedRefundMinor;
                $locked->resolution_type = 'refund';
            }

            $oldStatus = $locked->status;
            $locked->status = $decision;
            $locked->reviewed_by = $reviewerId;
            $locked->reviewed_at = now();
            $locked->decision_note = $note;
            $locked->save();

            ReturnEvent::create([
                'return_request_id' => $locked->id,
                'actor_user_id' => $reviewerId,
                'event_type' => 'return_reviewed',
                'from_status' => $oldStatus,
                'to_status' => $decision,
                'note' => $note,
                'metadata' => ['approved_refund_minor' => $locked->approved_refund_minor],
            ]);

            return $locked;
        }, 3);
    }
}
