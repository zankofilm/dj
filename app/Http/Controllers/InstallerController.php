<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class InstallerController extends Controller
{
    private function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    private function assertNotInstalled(): void
    {
        abort_if(file_exists($this->lockPath()), 404);
    }

    public function show(Request $request)
    {
        $this->assertNotInstalled();

        $token = $request->session()->get('installer_token');
        if (!$token) {
            $token = Str::random(64);
            $request->session()->put('installer_token', $token);
        }

        return response()
            ->view('installer.welcome', [
                'requirements' => $this->requirements(),
                'token' => $token,
            ])
            ->header('Cache-Control', 'no-store, private');
    }

    public function install(Request $request): Response
    {
        $this->assertNotInstalled();

        $key = 'marketplace-installer:'.sha1((string) $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, 'تعداد تلاش‌ها بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.');
        }
        RateLimiter::hit($key, 300);

        $data = $request->validate([
            'installer_token' => ['required', 'string', 'size:64'],
            'site_name' => ['required', 'string', 'max:120'],
            'site_url' => ['required', 'url', 'max:255'],
            'db_host' => ['required', 'string', 'max:190'],
            'db_port' => ['required', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:128'],
            'db_username' => ['required', 'string', 'max:128'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:190'],
            'admin_password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $sessionToken = (string) $request->session()->get('installer_token', '');
        if (!$sessionToken || !hash_equals($sessionToken, $data['installer_token'])) {
            abort(419, 'نشست نصب معتبر نیست. صفحه را تازه‌سازی کنید.');
        }

        if (!is_writable(base_path('.env')) && !is_writable(base_path())) {
            throw ValidationException::withMessages([
                'database' => 'فایل .env قابل نوشتن نیست. دسترسی فایل را در کنترل‌پنل هاست بررسی کنید.',
            ]);
        }

        $this->writeEnvironment($data);

        config([
            'app.name' => $data['site_name'],
            'app.url' => rtrim($data['site_url'], '/'),
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => (string) $data['db_port'],
            'database.connections.mysql.database' => $data['db_database'],
            'database.connections.mysql.username' => $data['db_username'],
            'database.connections.mysql.password' => $data['db_password'] ?? '',
            'database.connections.mysql.options' => extension_loaded('pdo_mysql') ? [] : null,
        ]);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'database' => 'اتصال دیتابیس برقرار نشد. نام دیتابیس، نام کاربری، رمز و میزبان را بررسی کنید.',
            ]);
        }

        if (User::where('email', $data['admin_email'])->exists()) {
            throw ValidationException::withMessages([
                'admin_email' => 'این ایمیل از قبل در دیتابیس وجود دارد.',
            ]);
        }

        $migrationExit = Artisan::call('migrate', ['--force' => true]);
        if ($migrationExit !== 0) {
            throw ValidationException::withMessages([
                'database' => 'اجرای ساختار دیتابیس ناموفق بود. گزارش خطا را در لاگ برنامه بررسی کنید.',
            ]);
        }

        $seedExit = Artisan::call('db:seed', ['--force' => true]);
        if ($seedExit !== 0) {
            throw ValidationException::withMessages([
                'database' => 'آماده‌سازی داده‌های پایه ناموفق بود. گزارش خطا را در لاگ بررسی کنید.',
            ]);
        }

        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'status' => 'active',
        ]);

        $role = Role::where('key', 'super-admin')->first();
        if (!$role) {
            throw ValidationException::withMessages([
                'database' => 'نقش مدیر کل در داده‌های پایه پیدا نشد.',
            ]);
        }
        $admin->roles()->syncWithoutDetaching([$role->id]);

        $lockDirectory = dirname($this->lockPath());
        if (!is_dir($lockDirectory) && !mkdir($lockDirectory, 0750, true) && !is_dir($lockDirectory)) {
            throw ValidationException::withMessages([
                'database' => 'ساخت پوشه قفل نصب ناموفق بود.',
            ]);
        }

        $written = file_put_contents($this->lockPath(), now()->toISOString().PHP_EOL, LOCK_EX);
        if ($written === false) {
            throw ValidationException::withMessages([
                'database' => 'ثبت قفل نصب ناموفق بود. سایت را عمومی نکنید و با پشتیبانی هاست تماس بگیرید.',
            ]);
        }
        @chmod($this->lockPath(), 0640);

        $request->session()->forget('installer_token');
        RateLimiter::clear($key);

        return response()->view('installer.complete', [
            'adminEmail' => $admin->email,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function requirements(): array
    {
        $checks = [
            'PHP نسخه ۸.۲ یا بالاتر' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'افزونه PDO' => extension_loaded('pdo'),
            'افزونه MySQL' => extension_loaded('pdo_mysql'),
            'افزونه Mbstring' => extension_loaded('mbstring'),
            'افزونه OpenSSL' => extension_loaded('openssl'),
            'افزونه Fileinfo' => extension_loaded('fileinfo'),
            'پوشه Storage قابل نوشتن' => is_writable(storage_path()),
            'پوشه Cache قابل نوشتن' => is_writable(base_path('bootstrap/cache')),
            'فایل .env موجود' => file_exists(base_path('.env')),
        ];

        return $checks;
    }

    private function writeEnvironment(array $data): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : file_get_contents(base_path('.env.example'));
        if ($contents === false) {
            throw ValidationException::withMessages(['database' => 'خواندن فایل تنظیمات محیط ناموفق بود.']);
        }

        $values = [
            'APP_NAME' => '"'.addcslashes($data['site_name'], "\\\"").'"',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => '"'.rtrim($data['site_url'], '/').'"',
            'APP_LOCALE' => 'fa',
            'APP_FALLBACK_LOCALE' => 'en',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $this->envValue($data['db_host']),
            'DB_PORT' => (string) $data['db_port'],
            'DB_DATABASE' => $this->envValue($data['db_database']),
            'DB_USERNAME' => $this->envValue($data['db_username']),
            'DB_PASSWORD' => $this->envValue($data['db_password'] ?? ''),
            'CACHE_STORE' => 'file',
            'SESSION_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'database',
        ];

        foreach ($values as $key => $value) {
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';
            if (preg_match($pattern, $contents)) {
                $replacement = $key.'='.$value;
                $contents = preg_replace_callback(
                    $pattern,
                    static fn () => $replacement,
                    $contents
                );
            } else {
                $contents .= PHP_EOL.$key.'='.$value;
            }
        }

        $temporary = $path.'.tmp';
        if (file_put_contents($temporary, $contents, LOCK_EX) === false || !rename($temporary, $path)) {
            @unlink($temporary);
            throw ValidationException::withMessages(['database' => 'ذخیره تنظیمات سایت ناموفق بود.']);
        }
        @chmod($path, 0640);
    }

    private function envValue(string $value): string
    {
        return '"'.str_replace(["\\", '"', "\r", "\n"], ["\\\\", '\\"', '', ''], $value).'"';
    }
}
