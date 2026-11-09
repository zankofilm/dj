<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MarketplaceInstallCommand extends Command
{
    protected $signature = 'marketplace:install
        {--admin-name= : Initial administrator name}
        {--admin-email= : Initial administrator email}';

    protected $description = 'Install the marketplace database and create the first administrator';

    public function handle(): int
    {
        if (file_exists(storage_path('app/installed.lock'))) {
            $this->error('Installation lock exists. Do not reinstall over live data.');
            return self::FAILURE;
        }

        if (config('app.env') === 'production' && config('app.debug')) {
            $this->error('APP_DEBUG must be false in production.');
            return self::FAILURE;
        }

        if (!config('app.key')) {
            $this->error('APP_KEY is missing. Generate it before installation.');
            return self::FAILURE;
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $exception) {
            $this->error('Database connection failed: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info('پیش‌بررسی نصب...');
        $preflight = $this->call('marketplace:preflight');
        if ($preflight !== self::SUCCESS) {
            $this->error('پیش‌بررسی ناموفق بود؛ نصب متوقف شد.');
            return self::FAILURE;
        }

        $name = $this->option('admin-name') ?: $this->ask('نام مدیر اصلی');
        $email = $this->option('admin-email') ?: $this->ask('ایمیل مدیر اصلی');
        $password = $this->secret('رمز عبور مدیر (حداقل ۱۲ نویسه)');

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 12) {
            $this->error('Invalid administrator details. No installation lock was written.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('An account with this email already exists.');
            return self::FAILURE;
        }

        $exit = Artisan::call('migrate', ['--force' => true]);
        $this->output->write(Artisan::output());
        if ($exit !== 0) {
            $this->error('Database migrations failed.');
            return self::FAILURE;
        }

        $exit = Artisan::call('db:seed', ['--force' => true]);
        $this->output->write(Artisan::output());
        if ($exit !== 0) {
            $this->error('Database seeding failed. Installation lock was not written.');
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $adminRole = \App\Models\Role::where('key', 'super-admin')->first();
        if (!$adminRole) {
            $this->error('System administrator role was not seeded.');
            return self::FAILURE;
        }
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        $lockDir = storage_path('app');
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0750, true);
        }

        if (file_put_contents(storage_path('app/installed.lock'), now()->toISOString().PHP_EOL, LOCK_EX) === false) {
            $this->error('Could not write installation lock. Restrict access and complete recovery manually.');
            return self::FAILURE;
        }
        @chmod(storage_path('app/installed.lock'), 0640);

        $this->info('Marketplace installation baseline completed.');
        $this->warn('This milestone is a foundation only; do not process live payments yet.');
        return self::SUCCESS;
    }
}
