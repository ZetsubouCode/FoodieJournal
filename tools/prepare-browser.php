<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/browser-environment.php';
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\Place;
use App\Models\RecommendationLabel;
use App\Models\User;
use App\Models\Visit;
use App\Services\MediaProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! file_exists($runtime.'/browser.sqlite')) {
    touch($runtime.'/browser.sqlite');
}
Artisan::call('migrate', ['--force' => true]);
Artisan::call('db:seed', ['--force' => true]);
// This short-lived process writes only the isolated QA database, never HTTP input.
Model::unguard();
$credentials = is_file($runtime.'/browser-credentials.json') ? json_decode(file_get_contents($runtime.'/browser-credentials.json'), true) : ['email' => 'qa@foodie.test', 'password' => bin2hex(random_bytes(20))];
$user = User::firstOrNew(['email' => $credentials['email']]);
$user->name = 'Pemilik QA';
$user->password = $credentials['password'];
$user->is_admin = true;
$user->save();
$category = Category::firstOrCreate(['slug' => 'qa-makanan'], ['name' => 'Makanan QA']);
$type = FoodType::firstOrCreate(['slug' => 'qa-mie-ayam'], ['name' => 'Mie ayam QA', 'category_id' => $category->id]);
$cuisine = Cuisine::firstOrCreate(['slug' => 'qa-jawa'], ['name' => 'Jawa QA']);
$style = CookingStyle::firstOrCreate(['slug' => 'qa-rebus'], ['name' => 'Rebus QA']);
$ingredient = Ingredient::firstOrCreate(['slug' => 'qa-ayam'], ['name' => 'Ayam QA']);
$recommendation = RecommendationLabel::firstOrCreate(['slug' => 'qa-kembali'], ['name' => 'Ingin kembali QA']);
$place = Place::firstOrCreate(['slug' => 'qa-warung-sore'], ['name' => 'Warung Sore (data QA)', 'city_name' => 'Semarang', 'address' => 'Alamat contoh untuk pengujian, bukan lokasi usaha nyata.']);
$headings = ['Semangkuk mie di penghujung sore', 'Singgah untuk sepiring nasi hangat', 'Rasa yang berubah pada kunjungan kedua', 'Teh tawar selepas hujan', 'Mencoba menu yang berbeda', 'Catatan makan siang singkat'];
foreach ($headings as $index => $heading) {
    $visit = Visit::firstOrCreate(['slug' => 'qa-kunjungan-'.($index + 1)], ['place_id' => $place->id, 'user_id' => $user->id, 'creation_token' => (string) Str::uuid(), 'title' => $heading, 'visited_at' => '2026-11-'.str_pad((string) (4 - min($index, 3)), 2, '0', STR_PAD_LEFT).' 10:00:00', 'status' => 'published', 'experience' => 'Data QA terisolasi, bukan ulasan nyata. Catatan ini menguji keterbacaan cerita, harga historis, dan navigasi jurnal pada beragam ukuran layar.', 'service_rating' => $index === 1 ? null : 3.5, 'total_spent_idr' => $index === 1 ? null : 60000]);
    $item = $visit->items()->firstOrCreate(['menu_name' => 'Mie ayam bakso (data QA)'], ['food_type_id' => $type->id, 'quantity' => 1, 'unit_price_idr' => 25000, 'taste_rating' => 4]);
    $item->cuisines()->sync([$cuisine->id]);
    $item->styles()->sync([$style->id]);
    $item->ingredients()->sync([$ingredient->id => ['knowledge_source' => 'Fixture QA']]);
    if ($index < 3) {
        $visit->recommendations()->sync([$recommendation->id]);
    }
    if (! $visit->media()->exists()) {
        $image = imagecreatetruecolor(1600, 1100);
        imagefill($image, 0, 0, imagecolorallocate($image, 55, 47, 38));
        imagefilledellipse($image, 800, 550, 850, 850, imagecolorallocate($image, 220, 212, 194));
        imagefilledellipse($image, 800, 550, 650, 650, imagecolorallocate($image, 167, 115, 54));
        imagestring($image, 5, 40, 1040, 'QA image fixture - not an actual visit photograph', imagecolorallocate($image, 245, 240, 231));
        ob_start();
        imagejpeg($image, null, 92);
        $bytes = ob_get_clean();
        imagedestroy($image);
        $uuid = (string) Str::uuid();
        $path = $uuid.'/source';
        Storage::disk('media')->put($path, $bytes);
        $media = $visit->media()->create(['uuid' => $uuid, 'original_path' => $path, 'mime_type' => 'image/jpeg', 'original_bytes' => strlen($bytes), 'width' => 1600, 'height' => 1100, 'sha256' => hash('sha256', $bytes), 'alt_text' => 'Gambar fixture QA, bukan foto kunjungan nyata']);
        app(MediaProcessor::class)->process($media);
        $visit->cover_media_id = $media->id;
        $visit->save();
        $item->media()->sync([$media->id]);
    }
}
$credentials['visitSlug'] = 'qa-kunjungan-1';
$credentials['placeSlug'] = $place->slug;
$credentials['visitId'] = Visit::where('slug', 'qa-kunjungan-1')->value('id');
file_put_contents($runtime.'/browser-credentials.json', json_encode($credentials));
@chmod($runtime.'/browser-credentials.json', 0600);
echo "Isolated browser fixture ready. Credentials stored privately in tools/.runtime/browser-credentials.json.\n";
