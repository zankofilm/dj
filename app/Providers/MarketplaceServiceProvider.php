<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SiteAppearanceSetting;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer([
            'storefront',
            'categories.show',
            'products.show',
            'cart.index',
            'checkout.index',
            'pages.show',
            'auth.login',
            'auth.register',
            'auth.register-complete',
            'auth.verify-mobile',
        ], function ($view) {
            $view->with('siteAppearance', SiteAppearanceSetting::allValues());
        });
    }
}
