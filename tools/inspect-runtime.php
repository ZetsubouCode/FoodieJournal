<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$data = [
    'environment' => 'Workspace local application, newly initialized without journal content',
    'php' => PHP_VERSION, 'laravel' => $app->version(), 'driver' => DB::connection()->getDriverName(),
    'tables' => Schema::getTableListing(null, false),
    'counts' => ['migrations' => DB::table('migration')->count(), 'allergens' => DB::table('allergen')->count(), 'admins' => DB::table('user')->where('is_admin', true)->count(), 'places' => DB::table('place')->count(), 'visits' => DB::table('visit')->count(), 'media' => DB::table('media')->count()],
];
if (in_array($data['driver'], ['mysql', 'mariadb'])) {
    $data['server'] = DB::selectOne('SELECT VERSION() as version')->version;
    $data['explain'] = DB::select("EXPLAIN SELECT id FROM visit WHERE status = 'published' AND deleted_at IS NULL ORDER BY visited_at DESC, id DESC LIMIT 18");
}
$fixture = new PDO('sqlite:'.__DIR__.'/.runtime/browser.sqlite');
$data['qa_sample_media'] = $fixture->query('SELECT original_bytes, width, height FROM media ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$data['qa_sample_variants'] = $fixture->query('SELECT variant, bytes, width, height FROM media_variant WHERE media_id = (SELECT MIN(id) FROM media) ORDER BY width')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__.'/../docs/qa/runtime-inspection.json', json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo "Runtime schema, empty journal counts and EXPLAIN recorded without credentials.\n";
