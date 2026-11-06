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
    }

    public function test_dossier_preserves_historical_values_nullable_criteria_and_editorial_endorsements(): void
    {
        $visit = Visit::factory()->published()->create([
            'experience' => "Paragraf pertama.\n\nParagraf berikutnya.", 'conclusion' => 'Catatan akhir yang tersimpan.',
            'total_spent_idr' => 93000, 'service_rating' => 3.5,
            'atmosphere_rating' => null, 'cleanliness_rating' => null, 'value_rating' => null,
        ]);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Harga historis', 'unit_price_idr' => 17000, 'taste_rating' => 4.5]);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Tanpa penilaian', 'unit_price_idr' => null, 'taste_rating' => null]);
        $visit->recommendations()->attach(RecommendationLabel::create(['name' => 'Pilihan khusus penulis']));
        $private = Visit::factory()->private()->create(['place_id' => $visit->place_id, 'title' => 'RELATED-PRIVATE-SECRET']);
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('dossier-hero', false)
            ->assertSee('Rp93.000')->assertSee('Rp17.000')->assertSee('Harga belum dicatat')
            ->assertSee('4,0')->assertSee('4,5')->assertSee('3,5')->assertDontSee('0,0')
            ->assertSee('Pilihan khusus penulis')->assertSee('Catatan akhir yang tersimpan.')
            ->assertDontSee($private->title)->assertSee('tasting-note--text', false);
        $visit->items()->update(['taste_rating' => null]);
        $visit->update(['service_rating' => null]);
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Penilaian belum dicatat')
            ->assertDontSee('0,0')->assertDontSee('Belum Dinilai');
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
    }
}
