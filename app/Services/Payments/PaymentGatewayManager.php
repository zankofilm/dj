<?php
namespace App\Services\Payments;
use App\Contracts\Payments\PaymentGateway;
use InvalidArgumentException;
class PaymentGatewayManager {
 private array $drivers=[];
 public function register(string $key,PaymentGateway $gateway): void { $this->drivers[$key]=$gateway; }
 public function driver(string $key): PaymentGateway {
  if(!isset($this->drivers[$key])) throw new InvalidArgumentException("درگاه پرداخت پیکربندی نشده است.");
  return $this->drivers[$key];
 }
}
