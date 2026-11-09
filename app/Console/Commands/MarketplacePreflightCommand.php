<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarketplacePreflightCommand extends Command
{
    protected $signature = 'marketplace:preflight';
    protected $description = 'Check shared-hosting requirements before installation';

    public function handle(): int
    {
        $checks = [];
        $checks['PHP >= 8.2'] = version_compare(PHP_VERSION, '8.2.0', '>=');
        foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'] as $extension) {
            $checks['PHP extension: '.$extension] = extension_loaded($extension);
        }
        $checks['APP_KEY configured'] = (bool) config('app.key');
        $checks['APP_DEBUG disabled'] = !config('app.debug');
        $checks['storage writable'] = is_writable(storage_path());
        $checks['bootstrap/cache writable'] = is_writable(base_path('bootstrap/cache'));

        try {
            DB::connection()->getPdo();
            $checks['Database connection'] = true;
        } catch (\Throwable $exception) {
            $checks['Database connection'] = false;
        }

        $failed = [];
        foreach ($checks as $label => $passed) {
            $this->line(($passed ? '<fg=green>PASS</>' : '<fg=red>FAIL</>').' '.$label);
            if (!$passed) {
                $failed[] = $label;
            }
        }

        if ($failed) {
            $this->newLine();
            $this->error('Preflight failed. Fix the listed requirements before installation.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Preflight passed. This only checks baseline requirements; it is not a security or production-readiness certification.');
        return self::SUCCESS;
    }
}
