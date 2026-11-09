<?php

namespace App\Services\Sms;

use App\Models\SmsVerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsOtpService
{
    public function __construct(private SmsGateway $gateway)
    {
    }

    public function send(string $phone, string $purpose = 'registration'): void
    {
        $cooldown = (int) config('sms.otp.resend_cooldown_seconds', 60);
        $hourLimit = (int) config('sms.otp.max_sends_per_hour', 5);

        $latest = SmsVerificationCode::where('phone', $phone)
            ->where('purpose', $purpose)->latest('id')->first();

        if ($latest && $latest->created_at->gt(now()->subSeconds($cooldown))) {
            throw ValidationException::withMessages(['phone' => 'برای ارسال مجدد کد کمی صبر کنید.']);
        }

        $sentRecently = SmsVerificationCode::where('phone', $phone)
            ->where('purpose', $purpose)->where('created_at', '>=', now()->subHour())->count();

        if ($sentRecently >= $hourLimit) {
            throw ValidationException::withMessages(['phone' => 'سقف ارسال کد برای این شماره در یک ساعت تکمیل شده است.']);
        }

        SmsVerificationCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $code = (string) random_int(100000, 999999);
        $record = SmsVerificationCode::create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addSeconds((int) config('sms.otp.ttl_seconds', 300)),
            'attempts' => 0,
        ]);

        try {
            $this->gateway->send($phone, "کد تأیید بازارگاه: {$code}\nاعتبار: ۵ دقیقه");
            $record->forceFill(['sent_at' => now()])->save();
        } catch (\Throwable $exception) {
            $record->delete();
            Log::warning('SMS OTP delivery failed', ['driver' => config('sms.driver'), 'exception' => get_class($exception)]);
            throw ValidationException::withMessages(['phone' => 'ارسال پیامک انجام نشد. تنظیمات پنل پیامکی را بررسی کنید.']);
        }
    }

    public function verify(string $phone, string $code, string $purpose = 'registration'): bool
    {
        return DB::transaction(function () use ($phone, $code, $purpose) {
            $record = SmsVerificationCode::where('phone', $phone)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$record || !$record->sent_at || $record->expires_at->isPast()) {
                return false;
            }

            $maxAttempts = (int) config('sms.otp.max_attempts', 5);
            if ($record->attempts >= $maxAttempts) {
                return false;
            }

            if (!Hash::check($code, $record->code_hash)) {
                $record->increment('attempts');
                return false;
            }

            $record->forceFill(['consumed_at' => now()])->save();
            return true;
        });
    }
}
