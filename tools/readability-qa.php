<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitItem;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

// Fixed, separate targets: no refresh, owner database, media or credentials.
$action = $argv[1] ?? '';
$fixture = $argv[2] ?? '';
if (! in_array($action, ['prepare', 'serve'], true) || ! in_array($fixture, ['empty', 'sparse'], true)) {
    throw new RuntimeException('Usage: php tools/readability-qa.php prepare|serve empty|sparse');
}
require __DIR__.'/browser-environment.php';
$database = __DIR__.'/.runtime/readability-'.$fixture.'.sqlite';
$port = $fixture === 'empty' ? 8013 : 8014;
foreach (['DB_DATABASE' => $database, 'APP_URL' => 'http://127.0.0.1:'.$port,
    'FOODIE_MEDIA_ROOT' => __DIR__.'/.runtime/readability-'.$fixture.'-media'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if ($action === 'serve') {
    if (! is_file($database)) {
        throw new RuntimeException('Prepare this fixture first.');
    }
    chdir(__DIR__.'/../public');
    passthru('"'.PHP_BINARY.'" -S 127.0.0.1:'.$port.' -t "'.__DIR__.'/../public" "'.__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"');
    exit;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $database) {
    throw new RuntimeException('Readability QA requires its dedicated SQLite target.');
}
if (! is_file($database)) {
    touch($database);
}
Artisan::call('migrate', ['--force' => true]);
if (! User::exists()) {
    $user = User::factory()->create();
    Visit::factory()->for($user)->private()->create(['slug' => 'qa-hidden', 'title' => 'RAHASIA-READABILITY']);
    Visit::factory()->for($user)->create(['slug' => 'qa-draft', 'title' => 'DRAFT-READABILITY']);
    if ($fixture === 'sparse') {
        $visit = Visit::factory()->for($user)->published()->create([
            'slug' => 'qa-tanpa-foto', 'title' => 'Catatan QA tanpa foto, penilaian, dan total belanja yang belum dicatat',
            'visited_at' => '2026-11-01 10:00:00', 'experience' => 'Fixture terisolasi untuk keterbacaan. Bukan pengalaman makan nyata.',
        ]);
        $visit->place->update(['name' => 'Warung QA dengan nama panjang di persimpangan jalan menjelang sore', 'city_name' => 'Yogyakarta', 'maps_url' => null]);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Hidangan QA dengan harga belum dicatat']);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Hidangan QA dengan harga nol tercatat', 'unit_price_idr' => 0]);
        for ($i = 1; $i <= 10; $i++) {
            $tag = Tag::create(['name' => 'Label panjang untuk catatan makan menjelang sore '.$i]);
            $visit->tags()->attach($tag);
        }
    }
}
if (Visit::publishedVisible()->count() !== ($fixture === 'empty' ? 0 : 1)) {
    throw new RuntimeException('Unexpected fixture contents; no data was cleared.');
}
echo 'Readability '.$fixture.' fixture ready on dedicated SQLite; serve on '.$port.'.'.PHP_EOL;
