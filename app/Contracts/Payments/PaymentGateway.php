<?php
namespace App\Contracts\Payments;
use App\Models\Payment;
interface PaymentGateway {
 public function initiate(Payment $payment): array;
 public function verifyWebhook(array $headers,string $rawBody): array;
 public function verifyTransaction(Payment $payment): array;
}
