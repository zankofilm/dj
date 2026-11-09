<?php

namespace App\Console\Commands;

use App\Models\PaymentWebhookEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecoverStalePaymentWebhooksCommand extends Command
{
    protected $signature = 'marketplace:webhooks:recover-stale
        {--minutes=10 : Minimum age of a processing lease before review}
        {--limit=100 : Maximum number of events to inspect}';

    protected $description = 'Move abandoned payment webhook processing leases to manual review';

    public function handle(): int
    {
        $minutes = filter_var($this->option('minutes'), FILTER_VALIDATE_INT);
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);

        if ($minutes === false || $minutes < 1 || $minutes > 10080
            || $limit === false || $limit < 1 || $limit > 1000) {
            $this->error('مقادیر minutes و limit خارج از محدوده مجاز هستند.');
            return self::INVALID;
        }

        $cutoff = now()->subMinutes($minutes);
        $ids = PaymentWebhookEvent::query()
            ->where('processing_status', 'processing')
            ->whereNotNull('processing_started_at')
            ->where('processing_started_at', '<=', $cutoff)
            ->orderBy('processing_started_at')
            ->limit($limit)
            ->pluck('id');

        $recovered = 0;
        foreach ($ids as $id) {
            $changed = DB::transaction(function () use ($id, $cutoff) {
                $event = PaymentWebhookEvent::whereKey($id)->lockForUpdate()->first();

                if (!$event || $event->processing_status !== 'processing'
                    || !$event->processing_started_at
                    || $event->processing_started_at->gt($cutoff)) {
                    return false;
                }

                $event->update([
                    'processing_status' => 'needs_review',
                    'failure_message' => 'پردازش Webhook بیش از زمان مجاز نیمه‌تمام مانده است؛ به دلیل ذخیره‌نشدن بدنه خام و سربرگ امضا، پردازش خودکار مجدد امن نیست و تطبیق دستی لازم است.',
                ]);

                return true;
            }, 3);

            if ($changed) {
                $recovered++;
            }
        }

        $this->info("رویدادهای منتقل‌شده به بررسی دستی: {$recovered}");
        $this->line('رویدادهای در حال پردازش فعال تغییر داده نشدند.');
        $this->warn('این فرمان پرداخت را دوباره اجرا نمی‌کند؛ فقط رویدادهای رهاشده را برای تطبیق امن علامت‌گذاری می‌کند.');

        return self::SUCCESS;
    }
}
