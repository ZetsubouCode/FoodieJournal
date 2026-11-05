<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\FoodType;
use App\Models\RecommendationLabel;
use App\Models\Tag;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Support\VisitCardTags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_card_uses_published_data_and_keeps_rating_recommendation_and_tag_destinations_distinct(): void
    {
        $visit = Visit::factory()->published()->create(['title' => str_repeat('Cerita yang panjang ', 8), 'visited_at' => '2026-10-28 12:00:00']);
        $visit->place->update(['city_name' => null]);
        $recommendation = RecommendationLabel::create(['name' => 'Ingin kembali']);
        $visit->recommendations()->attach($recommendation);
        $category = Category::create(['name' => 'Minuman']);
        $foodType = FoodType::create(['name' => 'Kopi', 'category_id' => $category->id]);
        $style = CookingStyle::create(['name' => 'Seduh']);
        $item = VisitItem::factory()->for($visit)->create(['food_type_id' => $foodType->id, 'taste_rating' => null]);
        $item->styles()->attach($style);
        foreach (range(1, 6) as $number) {
            $tag = Tag::create(['name' => 'Tag '.$number]);
            $visit->tags()->attach($tag);
        }
        Visit::factory()->private()->create(['title' => 'Catatan privat rahasia']);

        $response = $this->get('/explore?tab=visit');
        $response->assertOk()
            ->assertSee('Belum Dinilai')
            ->assertSee('Foto belum tersedia')
            ->assertSee('28 Okt 2026')
            ->assertSee('Tampilkan 7 tag lainnya')
            ->assertSee('Jelajahi rekomendasi Ingin kembali')
            ->assertSee('Jelajahi Minuman')
            ->assertSee('Jelajahi Kopi')
            ->assertDontSee('Catatan privat rahasia')
            ->assertDontSee('visit-card__arrow');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'class="visit-card__link"'));
        $this->assertSame(2, substr_count($html, 'class="visit-card__tag '));
        $this->assertStringContainsString('href="'.route('visit.show', $visit->slug).'"', $html);

        $tags = VisitCardTags::for($visit->load(['items.foodType.category', 'items.styles', 'tags']));
        $this->assertSame(['Minuman', 'Kopi', 'Seduh', 'Tag 1'], array_column(array_slice($tags, 0, 4), 'name'));
        $this->get($tags[0]['url'])->assertOk()->assertSee($visit->title);
        $this->get($tags[2]['url'])->assertOk()->assertSee($visit->title);
        $this->get(url('explore').'?recommendation%5B0%5D='.$recommendation->slug)->assertOk()->assertSee($visit->title);
    }

    public function test_card_with_no_tags_or_recommendations_has_no_disclosure(): void
    {
        $visit = Visit::factory()->published()->create(['service_rating' => 4.5]);
        $response = $this->get('/explore?tab=visit');
        $response->assertOk()->assertSee('4,5')->assertDontSee('visit-card__tags')->assertDontSee('visit-card__more')->assertDontSee('visit-card__recommendation');

        foreach (['Pedas', 'Hangat'] as $name) {
            $visit->tags()->attach(Tag::create(['name' => $name]));
            $response = $this->get('/explore?tab=visit');
            $response->assertOk()->assertSee('Jelajahi '.$name)->assertDontSee('visit-card__more');
        }
    }
}
