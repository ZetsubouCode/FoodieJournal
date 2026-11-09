<?php

use App\Models\Place;
use App\Models\User;
use App\Models\Visit;
use App\Services\MediaProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

require __DIR__.'/browser-environment.php';
$serving = PHP_SAPI === 'cli-server';
$action = $serving ? 'request' : ($argv[1] ?? '');
if (! in_array($action, ['prepare', 'serve', 'request'], true)) {
    throw new RuntimeException('Usage: php tools/p0-hardening-qa.php prepare|serve');
}
$database = __DIR__.'/.runtime/p0-hardening.sqlite';
foreach (['DB_DATABASE' => $database, 'APP_URL' => 'http://127.0.0.1:8017',
    'FOODIE_MEDIA_ROOT' => __DIR__.'/.runtime/p0-hardening-media', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if ($action === 'serve') {
    if (! is_file($database)) {
        throw new RuntimeException('Prepare the dedicated hardening fixture first.');
    }
    chdir(__DIR__.'/../public');
    passthru('"'.PHP_BINARY.'" -S 127.0.0.1:8017 -t "'.__DIR__.'/../public" "'.__FILE__.'"');
    exit;
}
require __DIR__.'/../vendor/autoload.php';
Carbon\Carbon::setTestNow('2026-11-09 12:00:00 UTC');
CarbonImmutable::setTestNow('2026-11-09 12:00:00 UTC');
if ($serving) {
    $asset = realpath(__DIR__.'/../public'.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($asset && is_file($asset) && str_starts_with($asset, realpath(__DIR__.'/../public').DIRECTORY_SEPARATOR)) {
        return false;
    }
}
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $database) {
    throw new RuntimeException('Hardening QA requires its fixed dedicated SQLite target.');
}
if ($serving) {
    // Real HTTP error rendering on this isolated harness only; no production routes change.
    Route::get('/p0h-error/{status}', function (int $status) {
        abort_unless(in_array($status, [403, 404, 419, 429, 500, 503]), 404);
        abort($status);
    });
    define('LARAVEL_START', microtime(true));
    header('X-Foodie-Hardening-Fixture: v1');
    $app->handleRequest(Request::capture());

    return;
}
if (! is_file($database)) {
    touch($database);
}
Artisan::call('migrate', ['--force' => true]);
if (! User::exists()) {
    $user = User::factory()->create();
    $place = Place::factory()->create(['slug' => 'qa-stable-cover', 'name' => 'Tempat QA dengan foto dari kunjungan lama']);
    $oldest = Visit::factory()->for($user)->for($place)->published()->create(['visited_at' => '2026-01-01', 'title' => 'Foto lama QA di halaman kedua']);
    $source = glob(__DIR__.'/../database/seeders/assets/*.jpg')[0];
    $bytes = file_get_contents($source);
    [$width, $height] = getimagesizefromstring($bytes);
    $uuid = (string) Str::uuid();
    Storage::disk('media')->put($uuid.'/source', $bytes);
    $media = $oldest->media()->create(['uuid' => $uuid, 'original_path' => $uuid.'/source', 'mime_type' => 'image/jpeg', 'original_bytes' => strlen($bytes), 'width' => $width, 'height' => $height, 'sha256' => hash('sha256', $bytes), 'alt_text' => 'Foto ilustrasi fixture QA, bukan pengalaman nyata']);
    if (! app(MediaProcessor::class)->process($media)) {
        throw new RuntimeException('Fixture photo processing failed; originals retained.');
    }
    $oldest->update(['cover_media_id' => $media->id]);
    Visit::factory()->for($user)->for($place)->published()->count(19)->create(['visited_at' => '2026-11-01', 'title' => 'Kunjungan QA terbaru tanpa foto']);
    $textPlace = Place::factory()->create(['slug' => 'qa-text-place', 'name' => str_repeat('Warung QA di persimpangan sore ', 4), 'address' => str_repeat('Alamat QA panjang di persimpangan jalan. ', 8), 'city_name' => null, 'province_name' => null, 'maps_url' => null]);
    Visit::factory()->for($user)->for($textPlace)->published()->create();
    Visit::factory()->for($user)->for($place)->private()->create(['title' => 'P0H-PRIVATE']);
    Visit::factory()->for($user)->for($place)->create(['title' => 'P0H-DRAFT']);
}
if (Visit::publishedVisible()->count() !== 21 || Visit::count() !== 23 || Place::count() !== 2) {
    throw new RuntimeException('Unexpected fixture contents; no data was cleared.');
}
echo 'Hardening QA ready: fixed isolated SQLite/media on 8017, 21 published / 2 hidden visits.'.PHP_EOL;
