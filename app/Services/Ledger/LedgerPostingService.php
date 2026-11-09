<?php

namespace App\Services\Ledger;

use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LedgerPostingService
{
    /**
     * Post one immutable, balanced journal entry.
     *
     * Each line: account_code, debit_minor, credit_minor, optional description
     * and optional seller_id/customer_user_id/order_id/payment_id dimensions.
     */
    public function post(array $data): JournalEntry
    {
        $currency = strtoupper((string) ($data['currency'] ?? ''));
        $eventKey = (string) ($data['event_key'] ?? '');
        $lines = $data['lines'] ?? [];

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new LedgerException('کد ارز معتبر نیست.');
        }
        if ($eventKey === '' || strlen($eventKey) > 150 || count($lines) < 2) {
            throw new LedgerException('شناسه رویداد یا ردیف‌های سند معتبر نیستند.');
        }

        $debitTotal = 0;
        $creditTotal = 0;
        foreach ($lines as $line) {
            $debit = (int) ($line['debit_minor'] ?? 0);
            $credit = (int) ($line['credit_minor'] ?? 0);
            if ($debit < 0 || $credit < 0 || (($debit > 0) === ($credit > 0))) {
                throw new LedgerException('هر ردیف باید دقیقاً بدهکار یا بستانکار باشد.');
            }
            $debitTotal += $debit;
            $creditTotal += $credit;
        }
        if ($debitTotal <= 0 || $debitTotal !== $creditTotal) {
            throw new LedgerException('جمع بدهکار و بستانکار سند برابر نیست.');
        }

        return DB::transaction(function () use ($data, $currency, $eventKey, $lines) {
            $existing = JournalEntry::where('event_key', $eventKey)->lockForUpdate()->first();
            if ($existing) {
                return $existing->load('lines');
            }

            $codes = collect($lines)->pluck('account_code')->unique()->values();
            $accounts = LedgerAccount::whereIn('code', $codes)->where('is_active', true)
                ->lockForUpdate()->get()->keyBy('code');

            if ($accounts->count() !== $codes->count()) {
                throw new LedgerException('یک یا چند حساب دفتر کل وجود ندارد یا غیرفعال است.');
            }

            foreach ($accounts as $account) {
                if ($account->currency !== $currency) {
                    throw new LedgerException('ارز حساب با ارز سند یکسان نیست.');
                }
            }

            $entry = JournalEntry::create([
                'public_id' => (string) Str::uuid(),
                'event_key' => $eventKey,
                'event_type' => $data['event_type'] ?? 'manual',
                'source_type' => $data['source_type'] ?? null,
                'source_id' => isset($data['source_id']) ? (string) $data['source_id'] : null,
                'currency' => $currency,
                'description' => $data['description'] ?? 'ثبت رویداد مالی',
                'status' => 'posted',
                'posted_at' => now(),
                'metadata' => $data['metadata'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            foreach (array_values($lines) as $index => $line) {
                $entry->lines()->create([
                    'ledger_account_id' => $accounts[$line['account_code']]->id,
                    'line_number' => $index + 1,
                    'debit_minor' => (int) ($line['debit_minor'] ?? 0),
                    'credit_minor' => (int) ($line['credit_minor'] ?? 0),
                    'currency' => $currency,
                    'description' => $line['description'] ?? null,
                    'seller_id' => $line['seller_id'] ?? null,
                    'customer_user_id' => $line['customer_user_id'] ?? null,
                    'order_id' => $line['order_id'] ?? null,
                    'payment_id' => $line['payment_id'] ?? null,
                ]);
            }

            return $entry->load('lines');
        }, 3);
    }

    /**
     * Correct a posted entry only by posting an equal-and-opposite reversal.
     */
    public function reverse(JournalEntry $entry, string $eventKey, string $reason, ?int $actorId = null): JournalEntry
    {
        if ($entry->reversal_of_id !== null) {
            throw new LedgerException('سند برگشتی را نمی‌توان دوباره برگشت زد.');
        }

        $entry->loadMissing('lines.account');

        $reversalLines = $entry->lines->map(function ($line) {
            return [
                'account_code' => $line->account->code,
                'debit_minor' => (int) $line->credit_minor,
                'credit_minor' => (int) $line->debit_minor,
                'description' => 'برگشت: '.$line->description,
                'seller_id' => $line->seller_id,
                'customer_user_id' => $line->customer_user_id,
                'order_id' => $line->order_id,
                'payment_id' => $line->payment_id,
            ];
        })->all();

        return DB::transaction(function () use ($entry, $eventKey, $reason, $actorId, $reversalLines) {
            $locked = JournalEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            if (JournalEntry::where('reversal_of_id', $locked->id)->exists()) {
                throw new LedgerException('برای این سند قبلاً سند برگشت ثبت شده است.');
            }

            $reversal = $this->post([
                'event_key' => $eventKey,
                'event_type' => 'reversal',
                'source_type' => JournalEntry::class,
                'source_id' => (string) $locked->id,
                'currency' => $locked->currency,
                'description' => $reason,
                'created_by' => $actorId,
                'metadata' => ['reversal_of' => $locked->public_id],
                'lines' => $reversalLines,
            ]);

            $reversal->update(['reversal_of_id' => $locked->id]);

            return $reversal->refresh()->load('lines');
        }, 3);
    }
}
