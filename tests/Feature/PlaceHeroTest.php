<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlaceHeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('media');
    }

    private function cover(Visit $visit, string $status = 'ready', bool $available = true): Media
    {
        $media = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture/source', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 1600, 'height' => 1000, 'sha256' => str_repeat('a', 64)]);
        $media->variants()->create(['variant' => 'hero', 'path' => $media->uuid.'/hero.webp', 'mime_type' => 'image/webp', 'width' => 1600, 'height' => 1000, 'bytes' => 10, 'status' => $status]);
        if ($available) {
            Storage::disk('media')->put($media->uuid.'/hero.webp', 'fixture');
        }
        $visit->update(['cover_media_id' => $media->id]);

        return $media;
    }

    public function test_published_cover_and_rating_are_real_and_null_ratings_are_excluded(): void
    {
        $visit = Visit::factory()->published()->create(['service_rating' => 4.5]);
        Visit::factory()->published()->create(['place_id' => $visit->place_id]);
        $cover = $this->cover($visit);
        $response = $this->get('/place/'.$visit->place->slug)->assertOk()->assertSee($cover->uuid)->assertSee('1 kunjungan dinilai');
        $this->assertSame($cover->id, $response->viewData('coverMedia')->id);
        $this->assertEquals(4.5, $response->viewData('place')->rating_score);
        $this->assertSame(2, $response->viewData('visits')->total());
    }

    public function test_older_cover_on_later_history_page_is_stable_with_date_then_id_ordering(): void
    {
        $oldest = Visit::factory()->published()->create(['visited_at' => '2026-01-01']);
        $cover = $this->cover($oldest);
        Visit::factory()->published()->count(19)->create(['place_id' => $oldest->place_id, 'visited_at' => '2026-11-01']);
        foreach ([1, 2, 9] as $page) {
            $response = $this->get('/place/'.$oldest->place->slug.'?page='.$page)->assertOk();
            $this->assertSame($cover->id, $response->viewData('coverMedia')->id);
        }
        $this->get('/place/'.$oldest->place->slug.'?page=9')->assertSee('Halaman ini sudah kosong')->assertSee('Ke halaman pertama');
        $newer = Visit::factory()->published()->create(['place_id' => $oldest->place_id, 'visited_at' => $oldest->visited_at]);
        $newCover = $this->cover($newer);
        $this->assertSame($newCover->id, $this->get('/place/'.$oldest->place->slug)->viewData('coverMedia')->id);
    }

    public function test_unready_missing_and_deleted_covers_fall_back_to_an_older_published_cover(): void
    {
        $visit = Visit::factory()->published()->create(['visited_at' => '2026-01-01']);
        $cover = $this->cover($visit);
        foreach (['failed', 'pending', 'missing', 'deleted'] as $state) {
            $newer = Visit::factory()->published()->create(['place_id' => $visit->place_id, 'visited_at' => '2026-11-01']);
            $media = $this->cover($newer, in_array($state, ['failed', 'pending']) ? $state : 'ready', $state !== 'missing');
            if ($state === 'deleted') {
                $media->delete();
            }
        }
        $this->assertSame($cover->id, $this->get('/place/'.$visit->place->slug)->assertOk()->viewData('coverMedia')->id);
    }

    public function test_draft_private_and_cross_visit_covers_are_never_hero_candidates(): void
    {
        $visit = Visit::factory()->published()->create();
        foreach (['draft', 'private'] as $status) {
            $hidden = Visit::factory()->create(['place_id' => $visit->place_id, 'status' => $status]);
            $cover = $this->cover($hidden);
            $this->get('/place/'.$visit->place->slug)->assertDontSee($cover->uuid)->assertViewHas('coverMedia', null);
            $this->get('/media/'.$cover->uuid.'/hero')->assertNotFound();
            $visit->update(['cover_media_id' => $cover->id]);
            $this->get('/place/'.$visit->place->slug)->assertViewHas('coverMedia', null);
            $visit->update(['cover_media_id' => null]);
        }
    }

    public function test_missing_responsive_derivative_is_omitted_while_the_real_hero_stays_available(): void
    {
        $visit = Visit::factory()->published()->create();
        $cover = $this->cover($visit);
        $cover->variants()->create(['variant' => 'card', 'path' => $cover->uuid.'/missing-card.webp', 'mime_type' => 'image/webp', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);
        $media = $this->get('/place/'.$visit->place->slug)->assertOk()->viewData('coverMedia');
        $this->assertSame($cover->id, $media->id);
        $this->assertNull($media->variant('card'));
        $this->assertCount(1, $media->variants);
    }

    public function test_publication_and_variant_revocation_reselect_then_render_text_without_caching(): void
    {
        $visit = Visit::factory()->published()->create(['visited_at' => '2026-01-01']);
        $older = $this->cover($visit);
        $newerVisit = Visit::factory()->published()->create(['place_id' => $visit->place_id, 'visited_at' => '2026-11-01']);
        $newer = $this->cover($newerVisit);
        $url = '/place/'.$visit->place->slug;
        $this->assertSame($newer->id, $this->get($url)->viewData('coverMedia')->id);
        $newerVisit->update(['status' => 'private']);
        $this->assertSame($older->id, $this->get($url)->viewData('coverMedia')->id);
        $this->get('/media/'.$newer->uuid.'/hero')->assertNotFound();
        $older->variants()->update(['status' => 'failed']);
        $this->get($url)->assertViewHas('coverMedia', null)->assertSee('place-dossier--text', false);
        $this->get('/media/'.$older->uuid.'/hero')->assertNotFound();
        $visit->update(['status' => 'draft']);
        $this->get($url)->assertNotFound();
    }

    public function test_photo_free_place_preserves_long_optional_facts_and_zero_rated_count(): void
    {
        $visit = Visit::factory()->published()->create();
        $visit->place->update(['name' => str_repeat('Warung panjang ', 9), 'address' => str_repeat('Alamat panjang ', 20), 'city_name' => null, 'province_name' => null, 'maps_url' => null]);
        $response = $this->get('/place/'.$visit->place->slug)->assertOk()->assertViewHas('coverMedia', null)
            ->assertSee('place-dossier--text', false)->assertSee($visit->place->address)->assertSee('Belum Dinilai')->assertSee('0 kunjungan dinilai')
            ->assertDontSee('Buka Google Maps')->assertDontSee('place-dossier__photo', false);
        $this->assertNull($response->viewData('place')->rating_score);
    }

    public function test_cover_and_card_queries_do_not_grow_per_history_visit(): void
    {
        $visit = Visit::factory()->published()->create();
        $this->cover($visit);
        $url = '/place/'.$visit->place->slug;
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $single = count(DB::getQueryLog());
        Visit::factory()->published()->count(17)->create(['place_id' => $visit->place_id]);
        DB::flushQueryLog();
        $this->get($url)->assertOk();
        $this->assertSame($single, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }
}
