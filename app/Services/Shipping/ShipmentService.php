<?php

namespace App\Services\Shipping;

use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    public function create(SellerOrder $sellerOrder, string $providerKey, array $recipient, array $parcels): Shipment
    {
        if (!in_array($sellerOrder->status, ['paid', 'processing', 'packed'], true)) {
            throw new \RuntimeException('وضعیت سفارش فروشنده برای ایجاد مرسوله مناسب نیست.');
        }

        $provider = app(ShippingProviderManager::class)->driver($providerKey);
        $result = $provider->createShipment($sellerOrder, $recipient, $parcels);

        if (empty($result['provider_reference']) || (int) ($result['shipping_minor'] ?? -1) < 0
            || strtoupper((string) ($result['currency'] ?? '')) !== strtoupper($sellerOrder->currency)) {
            throw new \RuntimeException('پاسخ شرکت حمل‌ونقل معتبر نیست.');
        }

        return DB::transaction(function () use ($sellerOrder, $providerKey, $recipient, $result) {
            $locked = SellerOrder::whereKey($sellerOrder->id)->lockForUpdate()->firstOrFail();

            $shipment = Shipment::create([
                'public_id' => (string) Str::uuid(),
                'seller_order_id' => $locked->id,
                'provider_key' => $providerKey,
                'status' => 'label_created',
                'tracking_number' => $result['tracking_number'] ?? null,
                'label_reference' => $result['label_reference'] ?? $result['provider_reference'],
                'shipping_minor' => (int) $result['shipping_minor'],
                'currency' => $result['currency'],
                'recipient_snapshot' => $recipient,
                'provider_metadata' => $result['metadata'] ?? [],
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'event_type' => 'shipment_created',
                'status' => 'label_created',
                'description' => 'مرسوله ایجاد شد.',
                'occurred_at' => now(),
            ]);

            return $shipment;
        }, 3);
    }

    public function recordProviderEvent(Shipment $shipment, array $event, string $rawBody): Shipment
    {
        if (empty($event['valid']) || empty($event['status'])) {
            throw new \RuntimeException('رویداد شرکت حمل‌ونقل معتبر نیست.');
        }

        return DB::transaction(function () use ($shipment, $event, $rawBody) {
            $locked = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();

            if (!empty($event['event_id'])) {
                $duplicate = ShipmentEvent::where('shipment_id', $locked->id)
                    ->where('provider_event_id', $event['event_id'])->exists();
                if ($duplicate) {
                    return $locked;
                }
            }

            $allowed = [
                'label_created', 'picked_up', 'in_transit', 'out_for_delivery',
                'delivered', 'delivery_failed', 'returned_to_sender', 'cancelled',
            ];
            if (!in_array($event['status'], $allowed, true)) {
                throw new \RuntimeException('وضعیت مرسوله ناشناخته است.');
            }

            // Keep terminal states stable when providers deliver stale callbacks out of order.
            $terminal = ['delivered', 'returned_to_sender', 'cancelled'];
            if (in_array($locked->status, $terminal, true) && $event['status'] !== $locked->status) {
                ShipmentEvent::create([
                    'shipment_id' => $locked->id,
                    'provider_event_id' => $event['event_id'] ?? null,
                    'event_type' => 'provider_tracking_update_ignored',
                    'status' => $event['status'],
                    'description' => 'رویداد دیررس ثبت شد اما وضعیت نهایی مرسوله تغییر نکرد.',
                    'payload_hash' => hash('sha256', $rawBody),
                    'occurred_at' => $event['occurred_at'] ?? now(),
                ]);
                return $locked->refresh();
            }

            ShipmentEvent::create([
                'shipment_id' => $locked->id,
                'provider_event_id' => $event['event_id'] ?? null,
                'event_type' => 'provider_tracking_update',
                'status' => $event['status'],
                'description' => $event['description'] ?? null,
                'location_label' => $event['location'] ?? null,
                'payload_hash' => hash('sha256', $rawBody),
                'occurred_at' => $event['occurred_at'] ?? now(),
            ]);

            $updates = ['status' => $event['status']];
            if ($event['status'] === 'picked_up') $updates['shipped_at'] = now();
            if ($event['status'] === 'delivered') $updates['delivered_at'] = now();
            if ($event['status'] === 'cancelled') $updates['cancelled_at'] = now();
            $locked->update($updates);

            if ($event['status'] === 'delivered') {
                $locked->sellerOrder()->whereIn('status', ['paid', 'processing', 'shipped'])
                    ->update(['status' => 'delivered']);
            }

            return $locked->refresh();
        }, 3);
    }
}
