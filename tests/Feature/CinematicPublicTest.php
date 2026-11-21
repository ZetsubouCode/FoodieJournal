<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\FoodType;
use App\Models\RecommendationLabel;
use App\Models\Visit;
use App\Models\VisitItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CinematicPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_surfaces_share_chrome_and_the_machine_lives_only_on_home(): void
    {
        $visit = Visit::factory()->published()->create();
        foreach (['/', '/explore', '/visit/'.$visit->slug, '/place/'.$visit->place->slug, '/missing-public-page'] as $url) {
            $response = $this->get($url);
            $this->assertSame(1, substr_count($response->getContent(), 'aria-label="Navigasi utama"'));
            $this->assertSame(1, substr_count($response->getContent(), 'class="site-footer"'));
            $response->assertSee('Rasa boleh lewat.')->assertSee('Ceritanya tinggal.');
        }
        $this->get('/')->assertOk()->assertSee('data-home-archive', false)->assertDontSee('data-filter-drawer', false)
            ->assertDontSee('Explore by curiosity')->assertDontSee('Buka kenangan')->assertDontSee('Jeda otomatis')
            ->assertDontSee('Catatan kuliner satu penulis.')->assertDontSee('Pilih koleksi untuk mengambil satu kenangan.')
            ->assertDontSee('Jeda cahaya')->assertDontSee('data-archive-explore', false);
        $this->get('/explore')->assertOk()->assertSee('The Curiosity Index')->assertSee('Memory Reel')
            ->assertDontSee('data-home-archive', false)->assertDontSee('data-machine', false);
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Catatan ini')->assertSee('Tentang tempat')
            ->assertDontSee('Lihat hidangan');
        $this->get('/place/'.$visit->place->slug)->assertOk()->assertDontSee('data-visit-motion-toggle', false);
    }

    public function test_dossier_preserves_historical_values_food_ratings_and_editorial_endorsements(): void
    {
        $visit = Visit::factory()->published()->create([
            'experience' => "Paragraf pertama.\n\nParagraf berikutnya.", 'conclusion' => 'Catatan akhir yang tersimpan.',
            'visited_at' => '2026-11-04 17:15:00',
            'total_spent_idr' => 93000,
            'pros' => "Staf membantu.\r\n \t\r\nMeja <bersih>.",
            'cons' => "Parkir terbatas.\nAntrean panjang.",
        ]);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Harga historis', 'unit_price_idr' => 17000, 'food_rating' => 4.5, 'notes' => 'Bumbu meresap.']);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Hidangan kedua', 'food_rating' => 3.5]);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Tanpa penilaian', 'unit_price_idr' => null, 'food_rating' => null]);
        $visit->recommendations()->attach(RecommendationLabel::create(['name' => 'Pilihan khusus penulis']));
        $private = Visit::factory()->private()->create(['place_id' => $visit->place_id, 'title' => 'RELATED-PRIVATE-SECRET']);
        $response = $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('dossier-hero', false)
            ->assertSee($visit->display_title)->assertSee($visit->place->name)->assertSee('5 November 2026')
            ->assertSee('href="#tasting">Lihat hidangan', false)
            ->assertSee('Rp93.000')->assertSee('Rp17.000')->assertSee('Harga belum dicatat')
            ->assertSee('Rerata hidangan')->assertSee('4,0')->assertSee('2 hidangan dinilai')
            ->assertSee('Rating hidangan: 4,5 dari 5')->assertSee('Rating hidangan: 3,5 dari 5')->assertSee('Belum Dinilai')
            ->assertSeeInOrder(['Bumbu meresap.', 'Staf membantu.', 'Meja <bersih>.', 'Parkir terbatas.', 'Antrean panjang.'])
            ->assertDontSee('<bersih>', false)->assertDontSee('assessment-title')->assertDontSee('Penilaian keseluruhan')->assertDontSee('0,0')
            ->assertSee('Pilihan khusus penulis')->assertSee('Catatan akhir yang tersimpan.')
            ->assertDontSee($private->title)->assertSee('tasting-note--text', false);
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(4, $xpath->query('//*[@aria-labelledby="observations-title"]//li')->length);
        $this->assertSame(2, $xpath->query('//span[contains(@class, "rating__star--half")]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="opinion"]//span[@class="dossier-stamp" and not(@role or @tabindex)]')->length);
        $visit->items()->update(['food_rating' => null]);
        $visit->update(['pros' => " \t\n", 'cons' => null]);
        $visit->recommendations()->detach();
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Belum Dinilai')->assertSee('03 / PENDAPAT')
            ->assertDontSee('0,0')->assertDontSee('observations-title')->assertDontSee('rating__stars')->assertDontSee('dossier-stamp');
    }

    public function test_dossier_omits_unready_gallery_and_preserves_partial_notes_free_prices_and_endorsements_without_story(): void
    {
        $visit = Visit::factory()->published()->create([
            'experience' => null, 'conclusion' => null, 'total_spent_idr' => null,
            'pros' => 'Staf menjelaskan pilihan tempat duduk.',
        ]);
        $visit->place->update(['maps_url' => null]);
        VisitItem::factory()->for($visit)->create(['unit_price_idr' => 0, 'food_rating' => null]);
        $visit->recommendations()->attach(RecommendationLabel::create(['name' => 'Pilihan tanpa skor']));
        $photo = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
        $photo->variants()->create(['variant' => 'thumbnail', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 300, 'height' => 200, 'bytes' => 10, 'status' => 'failed']);

        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Rp0')->assertSee('Belum dicatat')
            ->assertSee('Staf menjelaskan pilihan tempat duduk.')->assertSee('Belum Dinilai')
            ->assertDontSee('Kurang menyenangkan')
            ->assertSee('Rekomendasi pribadi')->assertSee('Pilihan tanpa skor')
            ->assertDontSee('id="story-title"', false)->assertDontSee('<blockquote', false)
            ->assertDontSee('id="galeri"', false)->assertDontSee('data-photo-viewer', false)->assertDontSee('Buka Google Maps');
    }

    public function test_dossier_gallery_supports_card_only_thumbnail_only_and_forty_ready_photos_without_eager_originals(): void
    {
        $visit = Visit::factory()->published()->create();
        for ($i = 0; $i < 40; $i++) {
            $photo = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
            $photo->variants()->create(['variant' => $i % 2 ? 'card' : 'thumbnail', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 300, 'height' => 200, 'bytes' => 10, 'status' => 'ready']);
        }
        $item = VisitItem::factory()->for($visit)->create();
        $item->media()->attach($visit->media()->skip(1)->first()->id);
        $response = $this->get('/visit/'.$visit->slug)->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(40, $xpath->query('//a[@data-view-photo]')->length);
        $this->assertSame(0, $xpath->query('//img[contains(@src,"/original")]')->length);
        $this->assertSame(0, $xpath->query('//link[contains(@href,"/original")]')->length);
        $this->assertStringEndsWith('/thumbnail', $xpath->query('//a[@data-view-photo]')->item(0)->getAttribute('href'));
        $second = $xpath->query('//a[@data-view-photo]')->item(1);
        $this->assertStringEndsWith('/card', $second->getAttribute('href'));
        $this->assertSame($second->getAttribute('data-view-photo'), $xpath->query('//a[@data-item-photo]')->item(0)->getAttribute('data-item-photo'));
    }

    public function test_curiosity_food_and_technique_photographs_belong_to_matching_published_items(): void
    {
        $category = Category::create(['name' => 'Camilan']);
        $type = FoodType::create(['name' => 'Pastry', 'category_id' => $category->id]);
        $style = CookingStyle::create(['name' => 'Panggang']);
        $visit = Visit::factory()->published()->create();
        $makePhoto = function ($owner) {
            $photo = $owner->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
            $photo->variants()->create(['variant' => 'card', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);

            return $photo;
        };
        $cover = $makePhoto($visit);
        $visit->update(['cover_media_id' => $cover->id]);
        $matching = $makePhoto($visit);
        $item = VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id]);
        $item->media()->attach($matching);
        $item->styles()->attach($style);
        $hidden = Visit::factory()->private()->create();
        $secret = $makePhoto($hidden);
        $privateItem = VisitItem::factory()->for($hidden)->create(['food_type_id' => $type->id]);
        $privateItem->media()->attach($secret);
        $privateItem->styles()->attach($style);

        $response = $this->get('/explore')->assertOk()->assertDontSee($secret->uuid);
        $this->assertSame($matching->id, $response->viewData('discovery')['food']['media']->id);
        $this->assertSame($matching->id, $response->viewData('discovery')['style']['media']->id);
        $this->assertSame($style->slug, $response->viewData('discovery')['style']['value']);
        $selected = $this->get('/explore?tab=food&style[]='.$style->slug)->assertOk()->assertDontSee($secret->uuid);
        $document = new \DOMDocument;
        @$document->loadHTML($selected->getContent());
        $link = (new \DOMXPath($document))->query('//a[@data-curiosity-key="style"]')->item(0);
        $this->assertSame('true', $link->getAttribute('data-selected'));
        $this->assertStringContainsString('Filter aktif', $link->getAttribute('aria-label'));
    }
}
