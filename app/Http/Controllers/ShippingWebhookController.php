<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\Shipping\ShipmentService;
use App\Services\Shipping\ShippingProviderManager;
use Illuminate\Http\Request;

class ShippingWebhookController extends Controller
{
    public function handle(string $provider, Request $request, ShippingProviderManager $providers, ShipmentService $shipments)
    {
        $event = $providers->driver($provider)->verifyWebhook($request->headers->all(), $request->getContent());
        if (empty($event['valid']) || empty($event['tracking_number'])) {
            abort(401, 'اعلان شرکت حمل‌ونقل معتبر نیست.');
        }

        $shipment = Shipment::where('provider_key', $provider)
            ->where('tracking_number', $event['tracking_number'])->firstOrFail();

        $shipments->recordProviderEvent($shipment, $event, $request->getContent());

        return response('', 200);
    }
}
