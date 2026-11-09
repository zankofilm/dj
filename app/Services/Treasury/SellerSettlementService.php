<?php

namespace App\Services\Treasury;

use App\Models\SellerOrder;
use App\Models\SellerSettlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerSettlementService
{
    /**
     * Create one seller payable record for a delivered/fulfilled seller order.
     * Commission is passed as an integer minor-unit amount calculated by the
     * versioned commission policy; this service never guesses a rate.
     */
    public function accrue(SellerOrder $sellerOrder, int $commissionMinor, int $adjustmentsMinor = 0): SellerSettlement
    {
        if ($commissionMinor < 0 || $commissionMinor > $sellerOrder->total_minor) {
            throw new SettlementException('مبلغ کمیسیون معتبر نیست.');
        }
        if (!in_array($sellerOrder->status, ['delivered', 'completed'], true)) {
            throw new SettlementException('فقط سفارش تحویل‌شده یا تکمیل‌شده قابل ورود به تسویه است.');
        }

        return DB::transaction(function () use ($sellerOrder, $commissionMinor, $adjustmentsMinor) {
            $locked = SellerOrder::whereKey($sellerOrder->id)->lockForUpdate()->firstOrFail();

            $existing = SellerSettlement::where('seller_order_id', $locked->id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $gross = (int) $locked->subtotal_minor;
            $net = $gross - $commissionMinor + $adjustmentsMinor;
            if ($net < 0) {
                throw new SettlementException('مبلغ خالص قابل پرداخت نمی‌تواند منفی باشد.');
            }

            return SellerSettlement::create([
                'public_id' => (string) Str::uuid(),
                'seller_id' => $locked->seller_id,
                'seller_order_id' => $locked->id,
                'currency' => $locked->currency,
                'gross_minor' => $gross,
                'commission_minor' => $commissionMinor,
                'adjustments_minor' => $adjustmentsMinor,
                'net_payable_minor' => $net,
                'status' => 'eligible',
                'eligible_at' => now(),
                'metadata' => [
                    'calculation_basis' => 'seller_order_subtotal',
                    'commission_policy' => 'externally_versioned-input',
                ],
            ]);
        }, 3);
    }
}
