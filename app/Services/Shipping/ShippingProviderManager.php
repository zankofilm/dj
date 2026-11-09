<?php

namespace App\Services\Shipping;

use App\Contracts\Shipping\ShippingProvider;
use InvalidArgumentException;

class ShippingProviderManager
{
    private array $providers = [];

    public function register(string $key, ShippingProvider $provider): void
    {
        $this->providers[$key] = $provider;
    }

    public function driver(string $key): ShippingProvider
    {
        if (!isset($this->providers[$key])) {
            throw new InvalidArgumentException('ارائه‌دهنده حمل‌ونقل پیکربندی نشده است.');
        }

        return $this->providers[$key];
    }
}
