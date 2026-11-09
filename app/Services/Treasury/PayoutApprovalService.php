<?php

namespace App\Services\Treasury;

use App\Models\SellerPayout;
use Illuminate\Support\Facades\DB;

class PayoutApprovalService
{
    public function approve(SellerPayout $payout, int $approverId): SellerPayout
    {
        return DB::transaction(function () use ($payout, $approverId) {
            $locked = SellerPayout::whereKey($payout->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'requested') {
                throw new SettlementException('این درخواست در وضعیت قابل تأیید نیست.');
            }
            if ($locked->seller_id === $approverId) {
                throw new SettlementException('فروشنده نمی‌تواند درخواست تسویه خود را تأیید کند.');
            }

            $locked->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $approverId,
            ]);

            $locked->settlements()->update(['status' => 'approved']);
            return $locked->refresh();
        }, 3);
    }
}
