<?php

namespace Database\Seeders;

use App\Models\Allergen;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Place;
use App\Models\RecommendationLabel;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use App\Services\MediaProcessor;
use App\Services\SaveVisit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DemoJournalSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo content is available only in local and testing environments.');
        }

        $owner = User::where('is_admin', true)->orderBy('id')->first();
        if (! $owner) {
            throw new \RuntimeException('Create the journal owner with php artisan foodie:admin before seeding demo content.');
        }

        $data = require __DIR__.'/data/demo-journal.php';
        // Check all bundled sources before writing anything. Seeding never needs a network connection.
        foreach (collect($data['places'])->pluck('items')->flatten(1)->pluck('photo')->filter()->unique() as $photo) {
            if (! is_file(__DIR__.'/assets/'.$photo.'.jpg')) {
                throw new \RuntimeException('Missing bundled demo photo: '.$photo);
            }
        }

        $this->call(DemoTaxonomySeeder::class);
        $created = 0;
        foreach ($data['visits'] as $index => $entry) {
            $token = (string) Uuid::uuid5(Uuid::NAMESPACE_URL, 'foodie/demo-journal/v1/'.$entry['key']);
            // A stable token also preserves renamed, edited, unpublished, and soft-deleted demo visits.
            if (Visit::withTrashed()->where('creation_token', $token)->exists()) {
                continue;
            }
            $directories = [];
            try {
                $added = DB::transaction(function () use ($owner, $data, $entry, $index, $token, &$directories) {
                    $profile = $data['places'][$entry['place']];
                    $place = Place::withTrashed()->firstOrCreate(['slug' => 'demo-'.$entry['place']], [
                        'name' => $profile['name'], 'city_name' => $profile['city'],
                        'province_name' => $profile['province'],
                        'address' => $profile['area'].' (alamat ilustratif untuk data demo)',
                    ]);
                    if ($place->trashed()) {
                        return false;
                    }
                    $ratings = $entry['ratings'] ?? [4, 4.5, 4.5, 4];
                    $visit = new Visit;
                    $visit->forceFill([
                        'user_id' => $owner->id, 'place_id' => $place->id, 'creation_token' => $token,
                        'slug' => 'demo-'.$entry['key'], 'title' => $entry['title'],
                        'status' => $entry['status'] ?? 'published',
                        'visited_at' => CarbonImmutable::now(config('foodie.timezone'))->startOfDay()->subDays($index + 1)->setTime($profile['hour'], 30)->utc(),
                        'experience' => $entry['story']."\n\nCatatan demo untuk pratinjau situs. Tempat, harga, penilaian, dan informasi bahan bersifat fiktif; foto hanya ilustrasi.",
                        'conclusion' => $entry['conclusion'] ?? $profile['conclusion'],
                        'service_review' => $profile['service'], 'service_rating' => $ratings[0],
                        'atmosphere_review' => $profile['atmosphere'], 'atmosphere_rating' => $ratings[1],
                        'cleanliness_review' => 'Meja dan peralatan makan terlihat bersih saat kunjungan ini.', 'cleanliness_rating' => $ratings[2],
                        'value_review' => $profile['value'], 'value_rating' => $ratings[3],
                        'total_spent_idr' => array_key_exists('total', $entry) ? $entry['total'] : $profile['total'],
                    ])->save();
                    $visit->tags()->sync($this->ids(Tag::class, $entry['tags']));
                    $visit->recommendations()->sync($this->ids(RecommendationLabel::class, $entry['recommendations'] ?? []));

                    foreach ($profile['items'] as $order => $dish) {
                        $item = $visit->items()->create([
                            'menu_name' => $dish['name'], 'quantity' => 1,
                            'unit_price_idr' => $dish['price'], 'taste_rating' => $dish['rating'],
                            'food_type_id' => $this->ids(FoodType::class, [$dish['type']])[0],
                            'notes' => $dish['notes'], 'sort_order' => $order,
                        ]);
                        $item->cuisines()->sync($this->ids(Cuisine::class, [$dish['cuisine']]));
                        $item->styles()->sync($this->ids(CookingStyle::class, [$dish['style']]));
                        foreach ($this->ids(Ingredient::class, $dish['ingredients']) as $id) {
                            $item->ingredients()->attach($id, ['knowledge_source' => 'Contoh data demo, bukan keterangan penjual nyata.']);
                        }
                        foreach ($dish['allergens'] as $code => $status) {
                            $item->allergens()->attach(Allergen::where('code', $code)->sole()->id, [
                                'exposure_status' => $status, 'evidence_source' => 'Simulasi informasi menu untuk data demo.',
                                'notes' => 'Informasi fiktif untuk menguji tampilan; bukan rujukan keamanan pangan.',
                            ]);
                        }
                        if ($dish['photo'] && empty($entry['without_photos'])) {
                            $photo = $this->photo($visit, $dish, $order, $directories);
                            $item->media()->attach($photo);
                            if (! $visit->cover_media_id) {
                                $visit->update(['cover_media_id' => $photo->id]);
                            }
                        }
                    }
                    if ($visit->status === 'published') {
                        app(SaveVisit::class)->validatePublish($visit);
                    }

                    return true;
                });
                $created += (int) $added;
            } catch (\Throwable $error) {
                // Only directories created by this failed transaction are removed.
                foreach ($directories as $directory) {
                    Storage::disk('media')->deleteDirectory($directory);
                }
                throw $error;
            }
        }
        $this->command?->info("Demo journal: {$created} new visits. Existing content and owner credentials were preserved.");
    }

    private function ids(string $model, array $names): array
    {
        return array_map(fn ($name) => $model::where('slug', Str::slug($name))->sole()->id, $names);
    }

    private function photo(Visit $visit, array $dish, int $order, array &$directories): Media
    {
        $bytes = file_get_contents(__DIR__.'/assets/'.$dish['photo'].'.jpg');
        [$width, $height] = getimagesizefromstring($bytes);
        $uuid = (string) Str::uuid();
        $directories[] = $uuid;
        $path = $uuid.'/source';
        Storage::disk('media')->put($path, $bytes);
        $media = $visit->media()->create([
            'uuid' => $uuid, 'original_path' => $path, 'mime_type' => 'image/jpeg',
            'original_bytes' => strlen($bytes), 'width' => $width, 'height' => $height,
            'sha256' => hash('sha256', $bytes), 'sort_order' => $order,
            'alt_text' => 'Foto ilustrasi '.$dish['name'].' untuk catatan demo',
        ]);
        if (! app(MediaProcessor::class)->process($media)) {
            throw new \RuntimeException('Could not process demo photo: '.$dish['photo']);
        }

        return $media;
    }
}
