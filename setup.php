<?php
declare(strict_types=1);

/*
 * Standalone bootstrap installer.
 * This file intentionally runs before Laravel's router so the setup page can
 * be displayed even when vendor/ is missing. Installation itself is blocked
 * until the complete Composer runtime is present.
 */
$base = __DIR__;
$lock = $base . '/storage/app/installed.lock';
$vendorAutoload = $base . '/vendor/autoload.php';
$errors = [];
$success = false;
$installMutex = null;

if (is_file($lock)) {
    http_response_code(404);
    exit('نصب قبلاً انجام شده است. برای امنیت، نصب‌کننده غیرفعال شده است.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => $secure]);
    session_start();
}
if (empty($_SESSION['marketplace_setup_token'])) {
    $_SESSION['marketplace_setup_token'] = bin2hex(random_bytes(32));
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function env_quote(string $value): string {
    return '"' . str_replace(["\\", '"', "\r", "\n"], ["\\\\", '\\"', '', ''], $value) . '"';
}
function write_install_state(string $path, array $state): void {
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $temporary = $path . '.tmp-' . bin2hex(random_bytes(6));
    if (file_put_contents($temporary, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('ذخیره وضعیت نصب ناموفق بود.');
    }
    @chmod($temporary, 0640);
    if (!rename($temporary, $path)) {
        @unlink($temporary);
        throw new RuntimeException('ثبت امن وضعیت نصب ناموفق بود.');
    }
}

function check_requirements(string $base): array {
    return [
        'PHP نسخه ۸.۲ یا بالاتر' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'افزونه PDO' => extension_loaded('pdo'),
        'افزونه MySQL' => extension_loaded('pdo_mysql'),
        'افزونه Mbstring' => extension_loaded('mbstring'),
        'افزونه OpenSSL' => extension_loaded('openssl'),
        'افزونه cURL' => extension_loaded('curl'),
        'افزونه Fileinfo' => extension_loaded('fileinfo'),
        'افزونه Ctype' => extension_loaded('ctype'),
        'افزونه Tokenizer' => extension_loaded('tokenizer'),
        'افزونه XML' => extension_loaded('xml'),
        'افزونه DOM' => extension_loaded('dom'),
        'افزونه Filter' => extension_loaded('filter'),
        'افزونه Hash' => extension_loaded('hash'),
        'افزونه Session' => extension_loaded('session'),
        'افزونه PCRE' => extension_loaded('pcre'),
        'افزونه JSON' => extension_loaded('json'),
        'پوشه storage قابل نوشتن' => is_dir($base.'/storage') && is_writable($base.'/storage'),
        'پوشه storage/app قابل نوشتن' => is_dir($base.'/storage/app') && is_writable($base.'/storage/app'),
        'پوشه storage/framework قابل نوشتن' => is_dir($base.'/storage/framework') && is_writable($base.'/storage/framework'),
        'پوشه storage/logs قابل نوشتن' => is_dir($base.'/storage/logs') && is_writable($base.'/storage/logs'),
        'پوشه bootstrap/cache قابل نوشتن' => is_dir($base.'/bootstrap/cache') && is_writable($base.'/bootstrap/cache'),
        'فایل vendor/autoload.php موجود' => is_file($base.'/vendor/autoload.php'),
        'فریم‌ورک Laravel نصب شده' => is_file($base.'/vendor/laravel/framework/src/Illuminate/Foundation/Application.php'),
        'فایل الگوی .env.example موجود' => is_file($base.'/.env.example'),
        'پوشه public و ورودی سایت موجود' => is_file($base.'/public/index.php'),
    ];
}
$checks = check_requirements($base);
$requirementsOk = !in_array(false, $checks, true);

// Shared-hosting resource settings are guidance, not hard blockers.
$memoryRaw = trim((string) ini_get('memory_limit'));
$memoryMb = 0;
if ($memoryRaw === '-1') {
    $memoryMb = 999999;
} elseif (preg_match('/^([0-9]+)M$/i', $memoryRaw, $memoryMatch)) {
    $memoryMb = (int) $memoryMatch[1];
} elseif (preg_match('/^([0-9]+)G$/i', $memoryRaw, $memoryMatch)) {
    $memoryMb = (int) $memoryMatch[1] * 1024;
}
$uploadRaw = trim((string) ini_get('upload_max_filesize'));
$uploadMb = preg_match('/^([0-9]+)M$/i', $uploadRaw, $uploadMatch) ? (int) $uploadMatch[1] : 0;
$hostingGuidance = [
    'حافظه PHP حداقل ۱۲۸ مگابایت' => $memoryMb >= 128,
    'زمان اجرای PHP حداقل ۶۰ ثانیه' => (int) ini_get('max_execution_time') === 0 || (int) ini_get('max_execution_time') >= 60,
    'حداکثر حجم آپلود حداقل ۲۰ مگابایت' => $uploadMb >= 20,
    'افزونه Intl برای مرتب‌سازی و زبان‌ها' => extension_loaded('intl'),
    'OPcache برای عملکرد بهتر' => extension_loaded('Zend OPcache'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['_token'] ?? '');
    if (!hash_equals((string)$_SESSION['marketplace_setup_token'], $token)) {
        $errors[] = 'نشست نصب منقضی یا نامعتبر است. صفحه را تازه‌سازی کنید.';
    } elseif (!$requirementsOk) {
        $errors[] = 'همه پیش‌نیازها آماده نیستند. تا رفع موارد ناموفق، نصب امکان‌پذیر نیست.';
    } else {
        // Serialize installation requests to prevent two administrators racing migrations.
        $mutexPath = $base . '/storage/app/setup-running.lock';
        $mutexDir = dirname($mutexPath);
        if (!is_dir($mutexDir)) @mkdir($mutexDir, 0750, true);
        $installMutex = @fopen($mutexPath, 'c+');
        if (!$installMutex || !flock($installMutex, LOCK_EX | LOCK_NB)) {
            if (is_resource($installMutex)) fclose($installMutex);
            $installMutex = null;
            $errors[] = 'فرایند نصب دیگری در حال اجراست. چند دقیقه بعد دوباره تلاش کنید.';
        } else {
            register_shutdown_function(static function () use (&$installMutex): void {
                if (is_resource($installMutex)) {
                    flock($installMutex, LOCK_UN);
                    fclose($installMutex);
                }
            });

            // Persistent IP-based attempt throttling, independent from Laravel/Redis.
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $throttlePath = $base . '/storage/app/setup-throttle-' . hash('sha256', $ip) . '.json';
            $throttleHandle = @fopen($throttlePath, 'c+');
            if (!$throttleHandle || !flock($throttleHandle, LOCK_EX)) {
                if (is_resource($throttleHandle)) fclose($throttleHandle);
                $errors[] = 'کنترل امنیتی نصب در دسترس نیست؛ دسترسی نوشتن storage/app را بررسی کنید.';
            } else {
                $raw = stream_get_contents($throttleHandle);
                $attempts = json_decode($raw ?: '[]', true);
                if (!is_array($attempts)) $attempts = [];
                $attempts = array_values(array_filter($attempts, static fn($t) => is_int($t) && $t > time() - 900));
                if (count($attempts) >= 5) {
                    $errors[] = 'تعداد تلاش‌های نصب از این IP بیش از حد مجاز است. ۱۵ دقیقه بعد دوباره تلاش کنید.';
                } else {
                    $attempts[] = time();
                    ftruncate($throttleHandle, 0);
                    rewind($throttleHandle);
                    fwrite($throttleHandle, json_encode($attempts));
                    fflush($throttleHandle);
                }
                flock($throttleHandle, LOCK_UN);
                fclose($throttleHandle);
            }
        }

        if (!$errors) {
        $siteName = trim((string)($_POST['site_name'] ?? 'بازارگاه'));
        $siteUrl = trim((string)($_POST['site_url'] ?? ''));
        $dbHost = trim((string)($_POST['db_host'] ?? 'localhost'));
        $dbPort = (string)($_POST['db_port'] ?? '3306');
        $dbName = trim((string)($_POST['db_database'] ?? ''));
        $dbUser = trim((string)($_POST['db_username'] ?? ''));
        $dbPass = (string)($_POST['db_password'] ?? '');
        $adminName = trim((string)($_POST['admin_name'] ?? ''));
        $adminEmail = strtolower(trim((string)($_POST['admin_email'] ?? '')));
        $adminPass = (string)($_POST['admin_password'] ?? '');
        $adminPass2 = (string)($_POST['admin_password_confirmation'] ?? '');

        if ($siteName === '' || strlen($siteName) > 120) $errors[] = 'نام فروشگاه معتبر نیست.';
        if (!filter_var($siteUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($siteUrl, PHP_URL_SCHEME), ['https','http'], true)) $errors[] = 'آدرس سایت معتبر نیست.';
        if ($dbHost === '' || !preg_match('/^[A-Za-z0-9.:-]+$/', $dbHost)
            || !preg_match('/^[A-Za-z0-9_$-]+$/', $dbName)
            || !preg_match('/^[A-Za-z0-9_$-]+$/', $dbUser)
            || !ctype_digit($dbPort) || (int)$dbPort < 1 || (int)$dbPort > 65535) {
            $errors[] = 'اطلاعات دیتابیس معتبر نیست. میزبان، نام دیتابیس، کاربر و پورت را بررسی کنید.';
        }
        if ($adminName === '' || strlen($adminName) > 120) $errors[] = 'نام مدیر معتبر نیست.';
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'ایمیل مدیر معتبر نیست.';
        if (strlen($adminPass) < 12 || $adminPass !== $adminPass2) $errors[] = 'رمز مدیر باید حداقل ۱۲ نویسه باشد و با تکرار آن یکسان باشد.';

        if (!$errors) {
            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8]
                );
                // Refuse unrelated databases, but allow a safe retry of this install after a partial failure.
                $serverVersion = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
                if ($serverVersion === '') {
                    throw new RuntimeException('نسخه سرور MySQL/MariaDB قابل تشخیص نیست.');
                }
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                $statePath = $base . '/storage/app/install-state.json';
                $dbFingerprint = hash('sha256', implode('|', [$dbHost, $dbPort, $dbName, $dbUser]));
                $installState = is_file($statePath) ? json_decode((string)file_get_contents($statePath), true) : null;
                $isResume = is_array($installState)
                    && isset($installState['database_fingerprint'])
                    && hash_equals((string)$installState['database_fingerprint'], $dbFingerprint);
                if (count($tables) > 0 && !$isResume) {
                    throw new RuntimeException('دیتابیس انتخاب‌شده خالی نیست یا متعلق به نصب نیمه‌تمام همین بازارگاه نیست. از دیتابیس اختصاصی و خالی استفاده کنید.');
                }
                if ($isResume) {
                    $storedEmail = strtolower((string)($installState['admin_email'] ?? ''));
                    $storedSiteName = (string)($installState['site_name'] ?? '');
                    $storedSiteUrl = rtrim((string)($installState['site_url'] ?? ''), '/');
                    if ($storedEmail !== strtolower($adminEmail)
                        || $storedSiteName !== $siteName
                        || $storedSiteUrl !== rtrim($siteUrl, '/')) {
                        throw new RuntimeException('برای ادامه نصب نیمه‌تمام، ایمیل مدیر، نام فروشگاه و آدرس سایت باید با مرحله قبلی یکسان باشند.');
                    }
                }
                if (!$isResume) {
                    write_install_state($statePath, [
                        'database_fingerprint' => $dbFingerprint,
                        'started_at' => gmdate('c'),
                        'stage' => 'database_connected',
                        'admin_email' => $adminEmail,
                        'site_name' => $siteName,
                        'site_url' => rtrim($siteUrl, '/'),
                        'database_server_version' => $serverVersion,
                    ]);
                }

                $envPath = $base . '/.env';
                $envTemplate = is_file($base.'/.env.example') ? file_get_contents($base.'/.env.example') : '';
                if ($envTemplate === false || $envTemplate === '') {
                    throw new RuntimeException('فایل الگوی .env.example پیدا نشد.');
                }
                $existingKey = null;
                if ($isResume && is_file($envPath)) {
                    $existingEnv = (string)file_get_contents($envPath);
                    if (preg_match('/^APP_KEY=(.*)$/m', $existingEnv, $keyMatch)) {
                        $candidateKey = trim($keyMatch[1]);
                        if (preg_match('/^base64:[A-Za-z0-9+\/=]+$/', $candidateKey)) {
                            $existingKey = $candidateKey;
                        }
                    }
                }
                $key = $existingKey ?? ('base64:' . base64_encode(random_bytes(32)));
                $values = [
                    'APP_NAME' => env_quote($siteName),
                    'APP_ENV' => 'production',
                    'APP_KEY' => $key,
                    'APP_DEBUG' => 'false',
                    'APP_URL' => env_quote(rtrim($siteUrl, '/')),
                    'APP_LOCALE' => 'fa',
                    'APP_FALLBACK_LOCALE' => 'en',
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST' => env_quote($dbHost),
                    'DB_PORT' => $dbPort,
                    'DB_DATABASE' => env_quote($dbName),
                    'DB_USERNAME' => env_quote($dbUser),
                    'DB_PASSWORD' => env_quote($dbPass),
                    'CACHE_STORE' => 'file',
                    'SESSION_DRIVER' => 'file',
                    'QUEUE_CONNECTION' => 'database',
                ];
                foreach ($values as $name => $value) {
                    $pattern = '/^'.preg_quote($name, '/').'=.*/m';
                    if (preg_match($pattern, $envTemplate)) {
                        $replacement = $name.'='.$value;
                        $envTemplate = preg_replace_callback(
                            $pattern,
                            static fn() => $replacement,
                            $envTemplate
                        );
                    } else {
                        $envTemplate .= PHP_EOL.$name.'='.$value;
                    }
                }
                $tmp = $envPath.'.setup-tmp';
                if (file_put_contents($tmp, $envTemplate, LOCK_EX) === false || !rename($tmp, $envPath)) {
                    @unlink($tmp);
                    throw new RuntimeException('ذخیره تنظیمات محیط ممکن نشد. دسترسی فایل .env را بررسی کنید.');
                }
                @chmod($envPath, 0640);

                require $vendorAutoload;
                $app = require $base.'/bootstrap/app.php';
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->bootstrap();

                $exit = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                if ($exit !== 0) {
                    throw new RuntimeException('ساخت جداول دیتابیس ناموفق بود. لاگ برنامه را بررسی کنید.');
                }
                $exit = Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                if ($exit !== 0) {
                    throw new RuntimeException('ساخت داده‌های پایه ناموفق بود. دیتابیس را بررسی و در صورت نیاز پاک‌سازی کنید.');
                }
                $state = json_decode((string)file_get_contents($statePath), true) ?: [];
                $state['stage'] = 'database_seeded';
                write_install_state($statePath, $state);

                $userClass = App\Models\User::class;
                $roleClass = App\Models\Role::class;
                $admin = Illuminate\Support\Facades\DB::transaction(function () use (
                    $userClass, $roleClass, $adminEmail, $adminName, $adminPass, $statePath
                ) {
                    $state = json_decode((string)file_get_contents($statePath), true);
                    $expectedEmail = strtolower((string)($state['admin_email'] ?? ''));
                    if ($expectedEmail === '' || !hash_equals($expectedEmail, strtolower($adminEmail))) {
                        throw new RuntimeException('برای ادامه نصب نیمه‌تمام باید همان ایمیل مدیر مرحله قبل وارد شود.');
                    }

                    $role = $roleClass::where('key', 'super-admin')->first();
                    if (!$role) {
                        throw new RuntimeException('نقش مدیر کل در داده‌های پایه پیدا نشد.');
                    }

                    $admin = $userClass::where('email', $adminEmail)->lockForUpdate()->first();
                    $stage = (string) ($state['stage'] ?? '');

                    if (!$admin) {
                        if ($stage !== 'database_seeded') {
                            throw new RuntimeException('وضعیت حساب مدیر با مرحله نصب سازگار نیست؛ برای جلوگیری از اعطای دسترسی ناخواسته، نصب متوقف شد.');
                        }
                        $admin = $userClass::create([
                            'name' => $adminName,
                            'email' => $adminEmail,
                            'password' => Illuminate\Support\Facades\Hash::make($adminPass),
                            'status' => 'active',
                        ]);
                    } else {
                        $expectedAdminId = (int) ($state['admin_user_id'] ?? 0);
                        if ($stage !== 'admin_created' || $expectedAdminId < 1 || $expectedAdminId !== (int) $admin->id) {
                            throw new RuntimeException('حسابی با این ایمیل از قبل وجود دارد و به نصب جاری تعلق ندارد؛ برای امنیت، نقش مدیر کل به حساب موجود داده نشد.');
                        }
                    }

                    $admin->roles()->syncWithoutDetaching([$role->id]);
                    return $admin;
                });

                $state = json_decode((string)file_get_contents($statePath), true) ?: [];
                $state['stage'] = 'admin_created';
                $state['admin_user_id'] = $admin->id;
                write_install_state($statePath, $state);

                $lockDir = dirname($lock);
                if (!is_dir($lockDir) && !mkdir($lockDir, 0750, true) && !is_dir($lockDir)) {
                    throw new RuntimeException('ساخت مسیر قفل نصب ناموفق بود.');
                }
                if (file_put_contents($lock, gmdate('c').PHP_EOL, LOCK_EX) === false) {
                    throw new RuntimeException('ثبت قفل نصب ناموفق بود؛ سایت را عمومی نکنید.');
                }
                @chmod($lock, 0640);
                if (is_file($statePath)) @unlink($statePath);
                $_SESSION = [];
                session_destroy();
                $success = true;
            } catch (Throwable $ex) {
                error_log('[Marketplace setup] '.$ex->getMessage());
                $errors[] = $ex instanceof RuntimeException
                    ? $ex->getMessage()
                    : 'نصب با خطا متوقف شد. گزارش فنی در لاگ سرور ثبت شده است.';
            }
        }
        }
    }
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, private');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>نصب بازارگاه</title>
<style>
*{box-sizing:border-box}body{margin:0;padding:22px;background:#f6f4fb;color:#292437;font-family:Tahoma,Arial,sans-serif}
.wrap{max-width:780px;margin:30px auto;background:#fff;border:1px solid #e9e3f2;border-radius:22px;padding:28px;box-shadow:0 14px 45px #31165a0d}
h1{color:#59319d;margin:0 0 8px}.sub{color:#716a7c;line-height:2;font-size:13px}.checks{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:16px 0 24px}
.check{padding:10px;border-radius:9px;background:#faf9fc;font-size:12px}.ok{color:#16804b}.bad{color:#b42318}
section{border-top:1px solid #eee9f4;padding-top:15px;margin-top:22px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
label{display:block;font-size:12px;font-weight:bold;margin:13px 0 7px}input{width:100%;padding:12px;border:1px solid #dcd5e8;border-radius:10px;font:inherit;font-size:13px}
button{width:100%;padding:15px;margin-top:22px;border:0;border-radius:11px;background:#6339ad;color:white;font:inherit;font-weight:bold;cursor:pointer}
button:disabled{background:#aaa;cursor:not-allowed}.alert{padding:13px;border-radius:10px;margin:14px 0;background:#fff0ee;color:#a32218;font-size:13px;line-height:1.9}
.done{background:#e8f6ee;color:#126a3d}.note{font-size:11px;color:#777184;line-height:1.9}
@media(max-width:620px){.wrap{margin:0 auto;padding:20px}.grid,.checks{grid-template-columns:1fr}}
</style></head><body><main class="wrap">
<h1>راه‌اندازی بازارگاه</h1>
<p class="sub">نصب‌کننده مستقل بازارگاه؛ بدون Terminal و SSH. تمام مراحل از طریق همین صفحه و مرورگر انجام می‌شود. ابتدا کامل بودن بسته و پیش‌نیازهای هاست بررسی می‌شوند، سپس اتصال دیتابیس و حساب مدیر تنظیم خواهد شد.</p>
<h3>بررسی پیش‌نیازهای سرور</h3><div class="checks">
<?php foreach($checks as $label=>$pass): ?><div class="check <?= $pass?'ok':'bad' ?>"><?= $pass?'✓':'✕' ?> <?= e($label) ?></div><?php endforeach; ?>
</div>
<h3>تنظیمات پیشنهادی هاست اشتراکی</h3><div class="checks">
<?php foreach($hostingGuidance as $label=>$pass): ?><div class="check <?= $pass?'ok':'bad' ?>"><?= $pass?'✓':'!' ?> <?= e($label) ?></div><?php endforeach; ?>
</div>
<p class="note">موارد این بخش توصیه‌ای هستند و به‌تنهایی مانع نصب نمی‌شوند. افزونه Intl برای مرتب‌سازی و پردازش بهتر فارسی و کوردی مفید است؛ OPcache نیز سرعت اجرای PHP را بهتر می‌کند.</p>
<?php if($success): ?>
<div class="alert done"><strong>نصب اولیه انجام شد.</strong><br>قفل نصب ایجاد شده است. اکنون وارد سایت شوید و اطلاعات ورود مدیر را استفاده کنید.</div>
<a href="./" style="display:block;text-align:center;padding:13px;background:#6339ad;color:#fff;border-radius:10px;text-decoration:none">رفتن به فروشگاه</a>
<?php else: ?>
<?php foreach($errors as $error): ?><div class="alert"><?= e($error) ?></div><?php endforeach; ?>
<?php if(!$requirementsOk): ?><div class="alert">نصب هنوز امکان‌پذیر نیست. موارد قرمز را رفع کنید. اگر vendor/autoload.php موجود نیست، این ZIP هنوز بسته انتشار کامل نیست و باید نسخه Release همراه وابستگی‌های Composer تهیه شود.</div><?php endif; ?>
<form method="post" autocomplete="off">
<input type="hidden" name="_token" value="<?= e((string)$_SESSION['marketplace_setup_token']) ?>">
<section><h3>مشخصات فروشگاه</h3>
<label>نام فروشگاه</label><input name="site_name" maxlength="120" required value="<?= e((string)($_POST['site_name']??'بازارگاه')) ?>">
<label>آدرس سایت</label><input name="site_url" type="url" required placeholder="https://example.com" value="<?= e((string)($_POST['site_url']??'')) ?>"></section>
<section><h3>اطلاعات دیتابیس MySQL</h3><div class="grid">
<div><label>میزبان</label><input name="db_host" required value="<?= e((string)($_POST['db_host']??'localhost')) ?>"></div>
<div><label>پورت</label><input name="db_port" type="number" min="1" max="65535" required value="<?= e((string)($_POST['db_port']??'3306')) ?>"></div>
<div><label>نام دیتابیس</label><input name="db_database" required value="<?= e((string)($_POST['db_database']??'')) ?>"></div>
<div><label>نام کاربری دیتابیس</label><input name="db_username" required value="<?= e((string)($_POST['db_username']??'')) ?>"></div></div>
<label>رمز دیتابیس</label><input name="db_password" type="password" autocomplete="new-password"></section>
<section><h3>حساب مدیر اصلی</h3>
<label>نام مدیر</label><input name="admin_name" required maxlength="120" value="<?= e((string)($_POST['admin_name']??'')) ?>">
<label>ایمیل مدیر</label><input name="admin_email" type="email" required value="<?= e((string)($_POST['admin_email']??'')) ?>">
<div class="grid"><div><label>رمز عبور (حداقل ۱۲ نویسه)</label><input name="admin_password" type="password" minlength="12" required autocomplete="new-password"></div>
<div><label>تکرار رمز عبور</label><input name="admin_password_confirmation" type="password" minlength="12" required autocomplete="new-password"></div></div></section>
<button type="submit" <?= $requirementsOk?'':'disabled' ?>>بررسی و نصب بازارگاه</button>
<p class="note">برای جلوگیری از حذف اطلاعات، دیتابیس باید خالی و اختصاصی این نصب باشد. این نسخه بتا است و پیش از استفاده واقعی باید روی هاست آزمایشی بررسی شود.</p>
</form>
<?php endif; ?>
</main></body></html>
