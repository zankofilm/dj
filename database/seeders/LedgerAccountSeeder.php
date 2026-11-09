<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'وجوه نقد و بانک', 'account_type' => 'asset', 'normal_side' => 'debit'],
            ['code' => '1100', 'name' => 'وجوه در راه نزد PSP', 'account_type' => 'asset', 'normal_side' => 'debit'],
            ['code' => '2000', 'name' => 'بدهی به فروشندگان', 'account_type' => 'liability', 'normal_side' => 'credit'],
            ['code' => '2100', 'name' => 'بدهی بازپرداخت مشتریان', 'account_type' => 'liability', 'normal_side' => 'credit'],
            ['code' => '2200', 'name' => 'کارمزد پرداختنی PSP', 'account_type' => 'liability', 'normal_side' => 'credit'],
            ['code' => '4000', 'name' => 'درآمد کمیسیون بازارگاه', 'account_type' => 'revenue', 'normal_side' => 'credit'],
            ['code' => '5000', 'name' => 'هزینه کارمزد PSP', 'account_type' => 'expense', 'normal_side' => 'debit'],
            ['code' => '5100', 'name' => 'هزینه جبران و تعدیلات', 'account_type' => 'expense', 'normal_side' => 'debit'],
        ];

        foreach (['EUR', 'IRR'] as $currency) {
            foreach ($accounts as $account) {
                LedgerAccount::firstOrCreate(
                    ['code' => $account['code'].'-'.$currency],
                    [...$account, 'currency' => $currency, 'is_system' => true, 'is_active' => true]
                );
            }
        }
    }
}
