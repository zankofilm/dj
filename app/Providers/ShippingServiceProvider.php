<?php

namespace App\Providers;

use App\Services\Shipping\ShippingProviderManager;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ShippingProviderManager::class, fn () => new ShippingProviderManager());
    }
}
