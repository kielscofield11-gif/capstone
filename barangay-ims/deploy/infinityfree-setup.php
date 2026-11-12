<?php

/**
 * TEMPORARY InfinityFree setup runner. Delete this file from the web root
 * immediately after use.
 *
 * Shared hosts like InfinityFree have no SSH and no CLI, so `php artisan
 * migrate` cannot be run on the server. This script applies migrations (and
 * optionally the seeder) through the browser instead.
 *
 * Usage:
 *   1. Set SETUP_TOKEN to a long random value in the server .env.
 *   2. Copy this file into the web root, next to index.php:
 *        htdocs/setup.php
 *   3. Open https://<your-host>/setup.php?token=<SETUP_TOKEN>
 *   4. First run only: add &seed=1 to create the initial accounts, then
 *      remove the SEED_* passwords from .env.
 *   5. DELETE htdocs/setup.php right away. Never leave it on the server:
 *      anyone with the URL can reset the seeded account passwords.
 *
 * If the request times out (free hosts cap execution time), simply reload the
 * page: pending migrations resume, and the seeder uses updateOrCreate.
 */

use Illuminate\Support\Facades\Artisan;

header('Content-Type: text/plain; charset=utf-8');

$candidates = [
    __DIR__ . '/../barangay-ims/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

$autoloadPath = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $autoloadPath = $candidate;
        break;
    }
}

if ($autoloadPath === null) {
    http_response_code(500);
    exit("Could not locate vendor/autoload.php. Upload this script into the web root next to index.php, with the application one level up in barangay-ims/.\n");
}

require $autoloadPath;

$candidates = [
    __DIR__ . '/../barangay-ims/bootstrap/app.php',
    __DIR__ . '/../bootstrap/app.php',
    __DIR__ . '/bootstrap/app.php',
];

$bootstrapPath = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $bootstrapPath = $candidate;
        break;
    }
}

if ($bootstrapPath === null) {
    http_response_code(500);
    exit("Could not locate bootstrap/app.php.\n");
}

$app = require $bootstrapPath;
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$expected = (string) config('deploy.setup_token');
$provided = (string) ($_GET['token'] ?? '');

if ($expected === '' || ! hash_equals($expected, $provided)) {
    http_response_code(404);
    exit("Not found.\n");
}

$runSeed = ($_GET['seed'] ?? '') === '1';

echo "Barangay IMS setup runner\n";
echo "=========================\n\n";

echo "Running migrations (migrate --force)...\n";
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();

if ($runSeed) {
    echo "\nRunning seeder (db:seed --force)...\n";
    echo "NOTE: the seeder resets the seeded account passwords on every run.\n";
    Artisan::call('db:seed', ['--force' => true]);
    echo Artisan::output();
}

echo "\nDone.\n";
echo "IMPORTANT: delete setup.php from the web root now (htdocs/setup.php).\n";
