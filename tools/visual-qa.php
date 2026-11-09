<?php

use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DemoJournalSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/browser-environment.php';
$serving = PHP_SAPI === 'cli-server';
$action = $serving ? 'request' : ($argv[1] ?? '');
$fixture = $serving ? getenv('FOODIE_VISUAL_FIXTURE') : ($argv[2] ?? '');
if (! in_array($action, ['prepare', 'serve', 'request'], true) || ! in_array($fixture, ['photo', 'empty'], true)) {
    throw new RuntimeException('Usage: php tools/visual-qa.php prepare|serve photo|empty');
}
$database = __DIR__.'/.runtime/visual-'.$fixture.'.sqlite';
$port = $fixture === 'photo' ? 8015 : 8016;
foreach (['DB_DATABASE' => $database, 'APP_URL' => 'http://127.0.0.1:'.$port,
    'FOODIE_MEDIA_ROOT' => __DIR__.'/.runtime/visual-'.$fixture.'-media', 'FOODIE_VISUAL_FIXTURE' => $fixture,
    'SESSION_COOKIE' => 'foodie_visual_qa', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if ($action === 'serve') {
    if (! is_file($database)) {
        throw new RuntimeException('Prepare the visual fixture first.');
    }
    chdir(__DIR__.'/../public');
    passthru('"'.PHP_BINARY.'" -S 127.0.0.1:'.$port.' -t "'.__DIR__.'/../public" "'.__FILE__.'"');
    exit;
}
require __DIR__.'/../vendor/autoload.php';
Carbon\Carbon::setTestNow('2026-11-09 12:00:00 UTC');
Carbon\CarbonImmutable::setTestNow('2026-11-09 12:00:00 UTC');
if ($serving) {
    $asset = realpath(__DIR__.'/../public'.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $public = realpath(__DIR__.'/../public').DIRECTORY_SEPARATOR;
    if ($asset && is_file($asset) && str_starts_with($asset, $public)) {
        return false;
    }
}
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $database) {
    throw new RuntimeException('Visual QA requires its fixed dedicated SQLite target.');
}
if ($serving) {
    define('LARAVEL_START', microtime(true));
    header('X-Foodie-Visual-Fixture: '.$fixture.'-v1');
    $app->handleRequest(Illuminate\Http\Request::capture());
    return;
}
if (! is_file($database)) {
    touch($database);
}
Artisan::call('migrate', ['--force' => true]);
if (! User::exists()) {
    $owner = User::factory()->create(['name' => 'Pemilik visual QA', 'email' => 'visual@foodie.test', 'is_admin' => true]);
    if ($fixture === 'empty') {
        Visit::factory()->for($owner)->private()->create(['title' => 'VISUAL-PRIVATE']);
        Visit::factory()->for($owner)->create(['title' => 'VISUAL-DRAFT']);
    }
}
if ($fixture === 'photo') {
    Artisan::call('db:seed', ['--class' => DemoJournalSeeder::class, '--force' => true]);
}
if (Visit::publishedVisible()->count() !== ($fixture === 'photo' ? 20 : 0)) {
    throw new RuntimeException('Unexpected visual fixture contents; no data was cleared.');
}
echo 'Fixed-date visual '.$fixture.' fixture ready on dedicated SQLite; serve on '.$port.'.'.PHP_EOL;
