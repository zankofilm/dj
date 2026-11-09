<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsGateway
{
    public function send(string $phone, string $message): void
    {
        $driver = config('sms.driver', 'log');

        if ($driver === 'log') {
            if (app()->environment('production')) {
                throw new RuntimeException('Log SMS driver is disabled in production.');
            }
            Log::info('SMS development transport', ['to' => $phone, 'message' => $message]);
            return;
        }

        if ($driver === 'kavenegar') {
            $this->sendWithKavenegar($phone, $message);
            return;
        }

        if ($driver !== 'http') {
            throw new RuntimeException('SMS driver is not supported.');
        }

        $endpoint = config('sms.endpoint');
        $token = config('sms.token');
        if (!$endpoint || !$token || !filter_var($endpoint, FILTER_VALIDATE_URL) || !str_starts_with(strtolower($endpoint), 'https://')) {
            throw new RuntimeException('SMS HTTPS endpoint and token must be configured.');
        }

        $response = Http::acceptJson()->asJson()->withToken($token)
            ->timeout((int) config('sms.timeout', 10))
            ->post($endpoint, ['to' => $phone, 'message' => $message, 'sender' => config('sms.sender')]);

        if (!$response->successful()) {
            throw new RuntimeException('SMS provider rejected the message. HTTP '.$response->status());
        }
    }

    private function sendWithKavenegar(string $phone, string $message): void
    {
        $apiKey = (string) config('sms.kavenegar.api_key');
        $sender = (string) config('sms.kavenegar.sender');
        if ($apiKey === '') {
            throw new RuntimeException('Kavenegar API key is not configured.');
        }

        // Kavenegar expects Iranian domestic mobile numbers in 09xxxxxxxxx format.
        $receptor = str_starts_with($phone, '+98') ? '0'.substr($phone, 3) : $phone;
        if (!preg_match('/^09[0-9]{9}$/', $receptor)) {
            throw new RuntimeException('Kavenegar SMS delivery currently supports Iranian mobile numbers only.');
        }

        $url = 'https://api.kavenegar.com/v1/'.rawurlencode($apiKey).'/sms/send.json';
        $response = Http::asForm()->acceptJson()->timeout((int) config('sms.timeout', 10))->post($url, [
            'receptor' => $receptor,
            'message' => $message,
            'sender' => $sender ?: null,
        ]);

        if (!$response->successful() || (int) data_get($response->json(), 'return.status', 0) !== 200) {
            throw new RuntimeException('Kavenegar did not accept the SMS request.');
        }
    }
}
