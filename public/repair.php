<?php

/**
 * cPanel Repair Tool — zip upload ke baad 500 error fix.
 * Visit: https://crm.paartech.in/repair.php
 * DELETE after site works again.
 *
 * exec/shell_exec use nahi karta — sirf PHP + Artisan direct call.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$basePath = dirname(__DIR__);
$autoFix = isset($_GET['auto']) || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

header('Content-Type: text/html; charset=utf-8');

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function readEnv(string $basePath): array
{
    $vars = [];
    $file = $basePath.'/.env';
    if (! is_file($file)) {
        return $vars;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $vars[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    return $vars;
}

function writeEnvValue(string $basePath, string $key, string $value): bool
{
    $file = $basePath.'/.env';
    if (! is_file($file) || ! is_writable($file)) {
        return false;
    }
    $content = file_get_contents($file);
    $line = $key.'='.$value;
    if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
        $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content);
    } else {
        $content .= "\n".$line;
    }

    return file_put_contents($file, $content) !== false;
}

function generateAppKey(): string
{
    return 'base64:'.base64_encode(random_bytes(32));
}

function testDatabase(array $env): array
{
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $db = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    if ($db === '' || $user === '') {
        return ['ok' => false, 'message' => 'DB_DATABASE ya DB_USERNAME .env mein missing hai'];
    }

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        return ['ok' => true, 'message' => 'Database connection OK'];
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'DB Error: '.$e->getMessage()];
    }
}

function ensureWritable(string $path): bool
{
    if (! is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    @chmod($path, 0775);

    return is_dir($path) && is_writable($path);
}

function clearBootstrapCache(string $basePath): array
{
    $cacheDir = $basePath.'/bootstrap/cache';
    $removed = [];
    $failed = [];

    if (! is_dir($cacheDir)) {
        ensureWritable($cacheDir);
    }

    $patterns = ['config.php', 'routes-v7.php', 'routes.php', 'packages.php', 'services.php', 'events.php'];

    foreach ($patterns as $file) {
        $path = $cacheDir.'/'.$file;
        if (is_file($path)) {
            if (@unlink($path)) {
                $removed[] = $file;
            } else {
                $failed[] = $file;
            }
        }
    }

    // Also clear any other cached PHP files except .gitignore
    foreach (glob($cacheDir.'/*.php') ?: [] as $path) {
        $name = basename($path);
        if ($name === '.gitignore') {
            continue;
        }
        if (@unlink($path)) {
            if (! in_array($name, $removed, true)) {
                $removed[] = $name;
            }
        } else {
            $failed[] = $name;
        }
    }

    return ['removed' => $removed, 'failed' => $failed];
}

function runArtisan(string $command, array $params = []): array
{
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call($command, $params);

        return ['code' => $exitCode, 'output' => trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'Done'];
    } catch (Throwable $e) {
        return ['code' => 1, 'output' => $e->getMessage()];
    }
}

function checkPhpVersion(): array
{
    $version = PHP_VERSION;
    $ok = version_compare($version, '8.2.0', '>=');

    return [
        'ok' => $ok,
        'message' => $ok
            ? "PHP {$version} OK (Laravel 11 ke liye 8.2+ chahiye)"
            : "PHP {$version} purana hai — Laravel 11 ke liye PHP 8.2+ chahiye",
    ];
}

function checkVendor(string $basePath): array
{
    $autoload = $basePath.'/vendor/autoload.php';
    if (! is_file($autoload)) {
        return [
            'ok' => false,
            'message' => 'vendor/ folder MISSING — yeh sabse common 500 ka reason hai zip upload ke baad',
        ];
    }

    return ['ok' => true, 'message' => 'vendor/autoload.php mil gaya'];
}

function checkEnv(string $basePath): array
{
    if (! is_file($basePath.'/.env')) {
        return ['ok' => false, 'message' => '.env file missing — purani .env wapas lagao ya .env.example se copy karo'];
    }

    return ['ok' => true, 'message' => '.env file present'];
}

function checkAppKey(array $env): array
{
    $appKey = $env['APP_KEY'] ?? '';
    if ($appKey === '' || ! str_starts_with($appKey, 'base64:')) {
        return ['ok' => false, 'message' => 'APP_KEY invalid ya khali hai', 'needs_fix' => true];
    }

    return ['ok' => true, 'message' => 'APP_KEY valid lag raha hai'];
}

function checkStorage(string $basePath): array
{
    $dirs = [
        '/storage', '/storage/app', '/storage/app/public',
        '/storage/framework', '/storage/framework/cache',
        '/storage/framework/cache/data',
        '/storage/framework/sessions', '/storage/framework/views',
        '/storage/logs', '/bootstrap/cache',
    ];

    $bad = [];
    foreach ($dirs as $dir) {
        if (! ensureWritable($basePath.$dir)) {
            $bad[] = ltrim($dir, '/');
        }
    }

    if ($bad) {
        return ['ok' => false, 'message' => 'Writable nahi: '.implode(', ', $bad)];
    }

    return ['ok' => true, 'message' => 'storage/ aur bootstrap/cache writable (775)'];
}

// --- Run diagnostics ---
$checks = [];
$steps = [];
$errors = [];
$warnings = [];
$env = readEnv($basePath);
$laravelBootstrapped = false;

$checks['php'] = checkPhpVersion();
$checks['env'] = checkEnv($basePath);
$checks['vendor'] = checkVendor($basePath);
$checks['app_key'] = checkAppKey($env);
$checks['database'] = testDatabase($env);
$checks['storage'] = checkStorage($basePath);

$vendorOk = $checks['vendor']['ok'];
$envOk = $checks['env']['ok'];
$dbOk = $checks['database']['ok'];

// --- Auto-fix when requested ---
if ($autoFix) {
    $steps[] = ['Auto-fix shuru', ['code' => 0, 'output' => 'Repair mode ON']];

    // Fix APP_KEY only if broken
    if (! ($checks['app_key']['ok'] ?? false)) {
        $newKey = generateAppKey();
        if (writeEnvValue($basePath, 'APP_KEY', $newKey)) {
            $env['APP_KEY'] = $newKey;
            putenv('APP_KEY='.$newKey);
            $_ENV['APP_KEY'] = $newKey;
            $checks['app_key'] = checkAppKey($env);
            $steps[] = ['APP_KEY fix', ['code' => 0, 'output' => 'Naya APP_KEY save ho gaya']];
        } else {
            $errors[] = '.env writable nahi — APP_KEY fix nahi ho paya';
            $steps[] = ['APP_KEY fix', ['code' => 1, 'output' => 'Failed — .env permission check karo']];
        }
    } else {
        $steps[] = ['APP_KEY', ['code' => 0, 'output' => 'Pehle se valid — skip']];
    }

    // Storage permissions
    checkStorage($basePath);
    $checks['storage'] = checkStorage($basePath);
    $steps[] = ['Storage permissions', ['code' => $checks['storage']['ok'] ? 0 : 1, 'output' => $checks['storage']['message']]];

    // Clear bootstrap cache (always safe after zip upload)
    $cacheResult = clearBootstrapCache($basePath);
    $cacheMsg = empty($cacheResult['removed'])
        ? 'Koi stale cache file nahi thi (OK)'
        : 'Deleted: '.implode(', ', $cacheResult['removed']);
    if (! empty($cacheResult['failed'])) {
        $warnings[] = 'Cache delete fail: '.implode(', ', $cacheResult['failed']);
    }
    $steps[] = ['Bootstrap cache clear', ['code' => empty($cacheResult['failed']) ? 0 : 1, 'output' => $cacheMsg]];

    // Laravel bootstrap + artisan (only if vendor exists)
    if ($vendorOk && $envOk && $dbOk) {
        try {
            require $basePath.'/vendor/autoload.php';
            $app = require $basePath.'/bootstrap/app.php';
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            $laravelBootstrapped = true;
            $steps[] = ['Laravel bootstrap', ['code' => 0, 'output' => 'OK — app load ho gayi']];
        } catch (Throwable $e) {
            $errors[] = 'Laravel bootstrap fail: '.$e->getMessage();
            $steps[] = ['Laravel bootstrap', ['code' => 1, 'output' => $e->getMessage()."\n\n".$e->getTraceAsString()]];
        }

        if ($laravelBootstrapped) {
            $migrate = runArtisan('migrate', ['--force' => true]);
            $steps[] = ['Migrate (new tables/columns)', $migrate];
            if ($migrate['code'] !== 0) {
                $errors[] = 'Migrate fail — '.$migrate['output'];
            }

            runArtisan('config:clear');
            $configCache = runArtisan('config:cache');
            $steps[] = ['Config cache rebuild', $configCache];

            runArtisan('route:clear');
            $routeCache = runArtisan('route:cache');
            $steps[] = ['Route cache rebuild', $routeCache];

            runArtisan('view:clear');
            runArtisan('view:cache');
            $steps[] = ['View cache rebuild', ['code' => 0, 'output' => 'Done']];

            // Storage link
            $link = $basePath.'/public/storage';
            if (! is_link($link) && ! is_dir($link)) {
                @symlink($basePath.'/storage/app/public', $link);
            }
            $steps[] = ['Storage symlink', ['code' => (is_link($link) || is_dir($link)) ? 0 : 1, 'output' => is_link($link) ? 'OK' : 'Manual check karo']];
        }
    } elseif (! $vendorOk) {
        $errors[] = 'vendor/ missing — pehle composer install chalao (neeche commands dekho)';
        $steps[] = ['Laravel bootstrap', ['code' => 1, 'output' => 'SKIP — vendor missing']];
    }
}

$success = $autoFix && empty($errors) && $vendorOk && $laravelBootstrapped;
$loginUrl = rtrim($env['APP_URL'] ?? 'https://crm.paartech.in', '/').'/login';

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>CRM Repair</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 820px; margin: 40px auto; padding: 0 20px; background: #f9fafb; color: #111; }
        h1 { margin-bottom: 8px; }
        .sub { color: #6b7280; margin-bottom: 24px; }
        .ok { color: #059669; font-weight: 600; }
        .bad { color: #dc2626; font-weight: 600; }
        .warn { color: #d97706; font-weight: 600; }
        pre { background: #fff; border: 1px solid #e5e7eb; padding: 12px; overflow: auto; font-size: 13px; border-radius: 8px; white-space: pre-wrap; }
        .box { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 12px 0; }
        .success-box { background: #ecfdf5; border-color: #6ee7b7; }
        .danger-box { background: #fef2f2; border-color: #fecaca; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 15px; }
        .btn:hover { background: #4338ca; }
        table { width: 100%; border-collapse: collapse; }
        td, th { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <h1>🔧 CRM Repair Tool</h1>
    <p class="sub">Zip upload ke baad HTTP 500 fix — vendor, cache, migrate, permissions</p>

    <?php if (! $autoFix): ?>
        <div class="box">
            <p><strong>Pehle diagnosis dekho, phir auto-fix chalao.</strong></p>
            <form method="post" style="margin-top:12px">
                <button type="submit" class="btn">▶ Auto-Fix Chalao</button>
            </form>
            <p style="margin-top:8px;font-size:13px;color:#6b7280">Ya direct: <a href="?auto=1">repair.php?auto=1</a></p>
        </div>
    <?php endif; ?>

    <h2>Diagnosis</h2>
    <div class="box">
        <table>
            <?php foreach ($checks as $name => $result): ?>
                <tr>
                    <td><strong><?= h(ucfirst(str_replace('_', ' ', $name))) ?></strong></td>
                    <td class="<?= ($result['ok'] ?? false) ? 'ok' : 'bad' ?>"><?= ($result['ok'] ?? false) ? '✅ OK' : '❌ FAIL' ?></td>
                    <td><?= h($result['message'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if (! $vendorOk): ?>
        <div class="box danger-box">
            <p class="bad">⚠️ vendor/ folder missing — /login bhi 500 dega!</p>
            <p>Zip mein usually <code>vendor/</code> nahi hota. Purana server pe agar tha to zip extract ne delete kar diya hoga.</p>
            <p><strong>cPanel Terminal mein yeh chalao:</strong></p>
            <pre>cd ~/crm.paartech.in
curl -sS https://getcomposer.org/installer | php
php composer.phar config audit.block-insecure false
php composer.phar install --optimize-autoloader --no-dev --no-scripts</pre>
            <p>Composer ke baad is page par wapas aao aur <strong>Auto-Fix</strong> dubara chalao.</p>
        </div>
    <?php endif; ?>

    <?php if ($autoFix): ?>
        <?php if ($success): ?>
            <div class="box success-box">
                <p class="ok">✅ Repair successful!</p>
                <p><a href="/login">Login try karo → <?= h($loginUrl) ?></a></p>
                <p class="bad">⚠️ Ab <code>public/repair.php</code> DELETE kar dein (security).</p>
            </div>
        <?php elseif (! empty($errors)): ?>
            <div class="box danger-box">
                <p class="bad">❌ Repair incomplete</p>
                <ul><?php foreach ($errors as $err): ?><li class="bad"><?= h($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (! empty($warnings)): ?>
            <div class="box">
                <p class="warn">Warnings:</p>
                <ul><?php foreach ($warnings as $w): ?><li><?= h($w) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <h2>Repair Steps Log</h2>
        <?php foreach ($steps as [$label, $result]): ?>
            <div class="box">
                <strong><?= h($label) ?></strong>
                <span class="<?= ($result['code'] ?? 1) === 0 ? 'ok' : 'bad' ?>">
                    — <?= ($result['code'] ?? 1) === 0 ? 'OK' : 'FAIL' ?>
                </span>
                <?php if (! empty($result['output'])): ?>
                    <pre><?= h($result['output']) ?></pre>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="box">
        <strong>Terminal fallback (manual):</strong>
        <pre>cd ~/crm.paartech.in
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/packages.php bootstrap/cache/services.php
php artisan migrate --force
php artisan config:clear
php artisan config:cache
php artisan route:cache</pre>
    </div>

    <p style="font-size:13px;color:#6b7280">Tip: Code update ke baad hamesha purani <code>.env</code> rakho. Zip se <code>vendor/</code> overwrite mat hone do — agar delete ho gaya to composer install zaroori hai.</p>
</body>
</html>
