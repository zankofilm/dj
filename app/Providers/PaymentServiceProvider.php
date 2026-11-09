<?php
namespace App\Providers;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;
class PaymentServiceProvider extends ServiceProvider {
 public function register(): void { $this->app->singleton(PaymentGatewayManager::class,fn()=>new PaymentGatewayManager()); }
}
