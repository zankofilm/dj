<?php

namespace App\Contracts\Shipping;

use App\Models\SellerOrder;

interface ShippingProvider
{
    /** @return array{provider_reference:string,tracking_number?:string,label_reference?:string,shipping_minor:int,currency:string,metadata?:array} */
    public function createShipment(SellerOrder $sellerOrder, array $recipient, array $parcels): array;

    /** @return array{valid:bool,event_id?:string,tracking_number?:string,status?:string,description?:string,occurred_at?:string,location?:string} */
    public function verifyWebhook(array $headers, string $rawBody): array;

    public function cancelShipment(string $providerReference): array;
}
