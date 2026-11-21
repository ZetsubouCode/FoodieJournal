<?php

use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Database\Seeders\DemoJournalSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/browser-environment.php';
$serving = PHP_SAPI === 'cli-server';
$action = $serving ? 'request' : ($argv[1] ?? '');
$fixture = $serving ? getenv('FOODIE_VISUAL_FIXTURE') : ($argv[2] ?? '');
if (! in_array($action, ['prepare', 'serve', 'request'], true) || ! in_array($fixture, ['photo', 'empty', 'visit'], true)) {
    throw new RuntimeException('Usage: php tools/visual-qa.php prepare|serve photo|empty|visit');
}
$database = __DIR__.'/.runtime/visual-'.$fixture.'.sqlite';
$port = match ($fixture) {
    'photo' => 8015,
    'empty' => 8016,
    'visit' => 8018,
};
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
CarbonImmutable::setTestNow('2026-11-09 12:00:00 UTC');
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
    $app->handleRequest(Request::capture());

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
if ($fixture !== 'empty') {
    Artisan::call('db:seed', ['--class' => DemoJournalSeeder::class, '--force' => true]);
}
if (Visit::publishedVisible()->count() !== ($fixture === 'empty' ? 0 : 20)) {
    throw new RuntimeException('Unexpected visual fixture contents; no data was cleared.');
}
if ($fixture === 'visit') {
    $visit = Visit::where('slug', 'demo-semangkuk-hangat-blok-m')->sole();
    $visit->items()->where('menu_name', 'Ramen kuah shoyu')->sole()->update(['notes' => <<<'TEXT'
Kuahnya ringan dan masih terasa hangat sampai suapan terakhir. Aroma kedelai muncul lebih jelas setelah mi diaduk. Saya menikmati beberapa suapan pertama tanpa tambahan apa pun supaya rasa kuahnya tetap terasa.

Mi memberi sedikit perlawanan ketika digigit. Bagian kuning telur yang lembut menambah rasa gurih saat larut di kuah, sementara sayur memberi selingan yang lebih segar. Saya menyisakan telur untuk suapan terakhir.

Setelah mangkuk hampir habis, rasa kuah masih terasa seimbang. Catatan ini adalah data fiktif untuk memeriksa tampilan ulasan panjang dalam arsip Foodie.
TEXT]);
}
echo 'Fixed-date visual '.$fixture.' fixture ready on dedicated SQLite; serve on '.$port.'.'.PHP_EOL;
