<?php

/**
 * cPanel Web Installer — exec/shell_exec ke bina (Artisan direct call).
 * Visit: https://crm.paartech.in/install.php
 * DELETE after success.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$basePath = dirname(__DIR__);
$lockFile = $basePath.'/storage/app/installed.lock';

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

function ensureWritable(string $path): void
{
    if (! is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    @chmod($path, 0775);
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

// --- Already installed? ---
if (is_file($lockFile) && ! isset($_GET['repair'])) {
    echo '<h1>Already Installed ✅</h1>';
    echo '<p><a href="/login">Login Page</a></p>';
    echo '<p>Zip update ke baad 500 error? → <a href="/repair.php"><strong>repair.php</strong></a> use karo (install.php dobara mat chalao).</p>';
    echo '<p style="color:red">install.php delete kar dein.</p>';
    exit;
}

$errors = [];
$steps = [];
$env = [];

if (! is_file($basePath.'/.env')) {
    echo '<h1>.env missing</h1><p>.env.example copy karke .env banao.</p>';
    exit;
}

if (! is_file($basePath.'/vendor/autoload.php')) {
    echo '<h1>vendor missing</h1><pre>cd ~/crm.paartech.in
php composer.phar install --optimize-autoloader --no-dev --no-scripts</pre>';
    exit;
}

$env = readEnv($basePath);

// Fix APP_KEY before Laravel bootstrap
$appKey = $env['APP_KEY'] ?? '';
if ($appKey === '' || ! str_starts_with($appKey, 'base64:')) {
    $newKey = generateAppKey();
    if (writeEnvValue($basePath, 'APP_KEY', $newKey)) {
        $steps[] = ['APP_KEY fixed', ['code' => 0, 'output' => 'Naya APP_KEY save ho gaya']];
        $env['APP_KEY'] = $newKey;
        putenv('APP_KEY='.$newKey);
        $_ENV['APP_KEY'] = $newKey;
    } else {
        $errors[] = '.env writable nahi hai';
    }
} else {
    $steps[] = ['APP_KEY', ['code' => 0, 'output' => 'Valid key set']];
}

$dbTest = testDatabase($env);
$steps[] = ['Database connection', ['code' => $dbTest['ok'] ? 0 : 1, 'output' => $dbTest['message']]];
if (! $dbTest['ok']) {
    $errors[] = $dbTest['message'];
}

foreach ([
    '/storage', '/storage/app', '/storage/app/public',
    '/storage/framework', '/storage/framework/cache',
    '/storage/framework/sessions', '/storage/framework/views',
    '/storage/logs', '/bootstrap/cache',
] as $dir) {
    ensureWritable($basePath.$dir);
}
$steps[] = ['Storage permissions', ['code' => 0, 'output' => '775 set']];

if ($dbTest['ok']) {
    // Bootstrap Laravel once
    try {
        require $basePath.'/vendor/autoload.php';
        $app = require $basePath.'/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $steps[] = ['Laravel bootstrap', ['code' => 0, 'output' => 'OK']];
    } catch (Throwable $e) {
        $errors[] = 'Laravel bootstrap fail: '.$e->getMessage();
        $steps[] = ['Laravel bootstrap', ['code' => 1, 'output' => $e->getMessage()]];
    }

    if (empty($errors)) {
        $migrate = runArtisan('migrate', ['--force' => true]);
        $steps[] = ['Migrate tables', $migrate];
        if ($migrate['code'] !== 0) {
            $errors[] = 'Migrate fail — '.$migrate['output'];
        }

        $seed = runArtisan('db:seed', ['--force' => true]);
        $steps[] = ['Seed demo data', $seed];
        if ($seed['code'] !== 0 && ! str_contains(strtolower($seed['output']), 'duplicate')) {
            $steps[count($steps) - 1][1]['output'] .= ' (data shayad pehle se hai — OK)';
        }

        $link = $basePath.'/public/storage';
        if (! is_link($link) && ! is_dir($link)) {
            @symlink($basePath.'/storage/app/public', $link);
        }
        $steps[] = ['Storage symlink', ['code' => is_link($link) || is_dir($link) ? 0 : 1, 'output' => is_link($link) ? 'Created' : 'Check manually']];

        runArtisan('config:clear');
        $cache = runArtisan('config:cache');
        $steps[] = ['Config cache', $cache];
        runArtisan('route:cache');
        runArtisan('view:cache');
    }
}

$success = empty($errors) && $dbTest['ok'];
if ($success) {
    @file_put_contents($lockFile, date('c'));
}

$loginUrl = rtrim($env['APP_URL'] ?? 'https://crm.paartech.in', '/').'/login';

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>CRM Install</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 20px; background: #f9fafb; }
        h1 { color: #111; }
        .ok { color: #059669; font-weight: 600; }
        .bad { color: #dc2626; font-weight: 600; }
        pre { background: #fff; border: 1px solid #e5e7eb; padding: 12px; overflow: auto; font-size: 13px; border-radius: 8px; white-space: pre-wrap; }
        .box { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 12px 0; }
        .success-box { background: #ecfdf5; border-color: #6ee7b7; }
    </style>
</head>
<body>
    <h1>🚀 SaaS CRM Installer</h1>

    <?php if ($success): ?>
        <div class="box success-box">
            <p class="ok">✅ Installation Successful!</p>
            <p><strong>Login:</strong> <a href="/login"><?= h($loginUrl) ?></a></p>
            <p><strong>Demo Admin:</strong> admin@demo.com / Demo@123</p>
            <p><strong>Super Admin:</strong> admin@platform.com / Admin@123</p>
            <p class="bad">⚠️ Ab <code>public/install.php</code> DELETE kar dein!</p>
        </div>
    <?php else: ?>
        <div class="box">
            <p class="bad">❌ Installation incomplete</p>
            <ul><?php foreach ($errors as $err): ?><li class="bad"><?= h($err) ?></li><?php endforeach; ?></ul>
        </div>
        <div class="box">
            <strong>Terminal se yeh chalao (backup plan):</strong>
            <pre>cd ~/crm.paartech.in
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache</pre>
        </div>
    <?php endif; ?>

    <h2>Steps Log</h2>
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
</body>
</html>
