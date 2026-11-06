<?php

use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Tag;
use App\Models\Visit;
use Illuminate\Contracts\Console\Kernel;

// Add filter-drawer cases only to the pre-existing isolated browser fixture.
if (! isset($app)) {
    require __DIR__.'/browser-environment.php';
    require __DIR__.'/../vendor/autoload.php';
    $app = require __DIR__.'/../bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
}
if (config('database.default') !== 'sqlite' || realpath(config('database.connections.sqlite.database')) !== realpath(__DIR__.'/.runtime/browser.sqlite')) {
    throw new RuntimeException('Archive fixtures require the isolated browser database.');
}
$parent = Category::firstOrCreate(['slug' => 'qa-archive-parent'], ['name' => 'Kelompok arsip QA']);
Category::where('slug', 'qa-makanan')->update(['parent_id' => $parent->id]);
$tag = Tag::firstOrCreate(['slug' => 'qa-archive-tag'], ['name' => 'Tag arsip QA']);
$visits = Visit::whereIn('slug', array_map(fn ($index) => 'qa-kunjungan-'.$index, range(1, 6)))->get();
foreach ($visits as $visit) {
    $visit->tags()->syncWithoutDetaching([$tag->id]);
    foreach ($visit->items as $item) {
        for ($index = 1; $index <= 12; $index++) {
            $style = CookingStyle::firstOrCreate(['slug' => 'qa-style-'.$index], ['name' => 'Teknik arsip '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
            $item->styles()->syncWithoutDetaching([$style->id]);
        }
    }
}
echo "Prepared archive hierarchy, long style list and tag in isolated QA database.\n";
