<?php

namespace App\Services\Treasury;

use App\Models\SellerPayout;
use App\Models\SellerPayoutDestination;
use App\Models\SellerSettlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutRequestService
{
    /**
     * Reserve eligible settlement balances into a payout request.
     * Approval and actual bank transfer are separate controlled steps.
     */
    public function request(int $sellerId, int $destinationId, string $currency, string $idempotencyKey): SellerPayout
    {
        if (strlen($idempotencyKey) < 16 || strlen($idempotencyKey) > 100) {
            throw new SettlementException('کلید یکتای درخواست تسویه معتبر نیست.');
        }

        return DB::transaction(function () use ($sellerId, $destinationId, $currency, $idempotencyKey) {
            $existing = SellerPayout::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
            if ($existing) {
                if ($existing->seller_id !== $sellerId) {
                    throw new SettlementException('کلید درخواست متعلق به فروشنده دیگری است.');
                }
                return $existing;
            }

            $destination = SellerPayoutDestination::whereKey($destinationId)
                ->where('seller_id', $sellerId)
                ->where('currency', strtoupper($currency))
                ->where('verification_status', 'verified')
                ->lockForUpdate()
                ->firstOrFail();

            $settlements = SellerSettlement::where('seller_id', $sellerId)
                ->where('currency', strtoupper($currency))
                ->where('status', 'eligible')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $amount = (int) $settlements->sum('net_payable_minor');
            if ($amount <= 0) {
                throw new SettlementException('مانده قابل تسویه وجود ندارد.');
            }

            $payout = SellerPayout::create([
                'public_id' => (string) Str::uuid(),
                'seller_id' => $sellerId,
                'payout_destination_id' => $destination->id,
                'currency' => strtoupper($currency),
                'amount_minor' => $amount,
                'status' => 'requested',
                'idempotency_key' => $idempotencyKey,
                'requested_at' => now(),
            ]);

            foreach ($settlements as $settlement) {
                $payout->settlements()->attach($settlement->id, [
                    'allocated_minor' => $settlement->net_payable_minor,
                ]);
                $settlement->update(['status' => 'payout_requested']);
            }

            return $payout->load('settlements');
        }, 3);
    }
}
