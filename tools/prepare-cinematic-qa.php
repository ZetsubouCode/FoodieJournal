<?php

use App\Models\User;
use Database\Seeders\DemoJournalSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

// A NEW, isolated photographic fixture. Never boots against the owner's DB.
require __DIR__.'/browser-environment.php';
$cinematicDatabase = __DIR__.'/.runtime/cinematic.sqlite';
foreach (['DB_DATABASE' => $cinematicDatabase, 'FOODIE_MEDIA_ROOT' => __DIR__.'/.runtime/cinematic-media'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $cinematicDatabase) {
    throw new RuntimeException('Cinematic QA must use its dedicated SQLite target.');
}
if (! is_file($cinematicDatabase)) {
    touch($cinematicDatabase);
}
Artisan::call('migrate', ['--force' => true]);
if (! User::exists()) {
    User::factory()->create(['is_admin' => true]);
}
Artisan::call('db:seed', ['--class' => DemoJournalSeeder::class, '--force' => true]);
echo 'Cinematic QA ready: dedicated SQLite/media with labeled, bundled demo photography.'.PHP_EOL;
