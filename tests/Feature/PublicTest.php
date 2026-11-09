<?php

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\RecommendationLabel;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\ExploreQuery;
use App\Services\Slugger;
use App\Support\ExploreState;
use App\Support\PublicOptions;
use Database\Seeders\AllergenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function results(array $state = [])
    {
        return app(ExploreQuery::class)->build(new ExploreState($state))->get();
    }

    public function test_empty_public_routes_render_without_fake_data(): void
    {
        foreach (['/', '/explore', '/explore?tab=food', '/explore?tab=place', '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertSee('Belum ada kunjungan yang dipublikasikan');
        $this->get('/visit/missing')->assertNotFound();
        $this->get('/place/missing')->assertNotFound();
    }

    public function test_empty_states_use_only_published_existence_in_full_and_fragment_responses(): void
    {
        $hidden = Visit::factory()->private()->create(['title' => 'RAHASIA-EMPTY']);
        Visit::factory()->create(['title' => 'RAHASIA-DRAFT']);
        foreach ([[], ['X-Explore-Fragment' => '1']] as $headers) {
            $this->get('/explore?q=tidak-ada', $headers)->assertOk()
                ->assertSee('Arsip belum dipublikasikan')->assertViewHas('hasPublishedVisits', false)
                ->assertDontSee('RAHASIA-EMPTY')->assertDontSee('RAHASIA-DRAFT');
        }
        $hidden->update(['status' => 'published']);
        $this->get('/explore?tab=food')->assertOk()->assertSee('Belum ada hidangan yang dicatat')->assertSee('Lihat kunjungan');
        foreach ([[], ['X-Explore-Fragment' => '1']] as $headers) {
            $this->get('/explore?q=tidak-ada&view=list', $headers)->assertOk()
                ->assertSee('Tidak ada catatan yang cocok')->assertSee('Reset filter')
                ->assertSee('Hapus filter Cari: tidak-ada')->assertSee('tab=visit&amp;view=list', false);
        }
        $this->get('/explore?page=9')->assertOk()->assertSee('Halaman ini sudah kosong')->assertSee('Ke halaman pertama');
        $hidden->update(['status' => 'draft']);
        $this->get('/explore?page=9')->assertOk()->assertSee('Arsip belum dipublikasikan')->assertDontSee('Halaman ini sudah kosong');
    }

    public function test_partial_records_keep_unknown_rating_price_and_explicit_zero_distinct(): void
    {
        $visit = Visit::factory()->published()->create();
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Harga tidak diketahui']);
        VisitItem::factory()->for($visit)->create(['menu_name' => 'Harga nol tercatat', 'unit_price_idr' => 0]);
        $this->get('/')->assertOk()->assertSee('Belum ada rekomendasi pribadi yang dipublikasikan');
        $this->get('/explore')->assertOk()->assertSee('Belum Dinilai')->assertSee('Foto belum tersedia')->assertDontSee('0,0');
        $this->get('/explore?tab=food')->assertOk()->assertSee('Harga belum dicatat')->assertSee('Rp0')->assertSee('Belum Dinilai');
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Harga belum dicatat')->assertSee('Rp0')->assertSee('Penilaian belum dicatat');
        $this->get('/place/'.$visit->place->slug)->assertOk()->assertSee('Belum Dinilai')->assertDontSee('Buka Google Maps');
    }

    public function test_homepage_recent_order_curated_picks_and_discovery_use_published_data(): void
    {
        $older = Visit::factory()->published()->create(['title' => 'Catatan lebih lama', 'visited_at' => '2026-10-01 12:00:00']);
        $newer = Visit::factory()->published()->create(['title' => 'Catatan lebih baru', 'visited_at' => '2026-11-01 12:00:00', 'service_rating' => 5]);
        $label = RecommendationLabel::create(['name' => 'Pilihan penulis']);
        $older->recommendations()->attach($label);
        $category = Category::create(['name' => 'Makanan pilihan']);
        $type = FoodType::create(['name' => 'Hidangan pilihan', 'category_id' => $category->id]);
        $style = CookingStyle::create(['name' => 'Rebus pilihan']);
        $item = VisitItem::factory()->for($older)->create(['food_type_id' => $type->id]);
        $item->styles()->attach($style);
        $response = $this->get('/')->assertOk()->assertSeeInOrder(['Catatan lebih baru', 'Catatan lebih lama']);
        preg_match('/<section class="home-section" aria-labelledby="picks-title">(.*?)<\/section>/s', $response->getContent(), $picks);
        $this->assertNotEmpty($picks);
        $this->assertStringContainsString('Catatan lebih lama', $picks[1]);
        $this->assertStringNotContainsString('Catatan lebih baru', $picks[1]);
        // Taxonomy discovery lives on Explore; the homepage cabinet browses visits.
        $this->get('/explore')->assertSee('category%5B0%5D='.$category->slug, false)
            ->assertSee('style%5B0%5D='.$style->slug, false);
    }

    public function test_every_public_entry_point_excludes_private_content(): void
    {
        $public = Visit::factory()->published()->create(['title' => 'Cerita terbuka']);
        $hidden = Visit::factory()->private()->create(['title' => 'RAHASIA-PRIVATE-UNIK']);
        Visit::factory()->create(['title' => 'RAHASIA-DRAFT-UNIK']);
        $privateCuisine = Cuisine::create(['name' => 'Cuisine sangat rahasia']);
        $privateItem = VisitItem::factory()->for($hidden)->create(['menu_name' => 'HIDANGAN-RAHASIA']);
        $privateItem->cuisines()->attach($privateCuisine);
        foreach (['/', '/explore', '/explore?tab=food', '/explore?tab=place', '/sitemap.xml', '/visit/'.$public->slug, '/place/'.$public->place->slug] as $url) {
            $this->get($url)->assertOk()->assertDontSee('RAHASIA-PRIVATE-UNIK')->assertDontSee('RAHASIA-DRAFT-UNIK')->assertDontSee('HIDANGAN-RAHASIA')->assertDontSee('Cuisine sangat rahasia');
        }
        $this->get('/visit/'.$hidden->slug.'?preview=1')->assertNotFound();
        $this->get('/place/'.$hidden->place->slug)->assertNotFound();
        $this->getJson('/explore/count')->assertJson(['total' => 1]);
        $public->update(['status' => 'draft']);
        $this->getJson('/explore/count')->assertJson(['total' => 0]);
        $this->get('/sitemap.xml')->assertDontSee($public->slug);
        $this->get('/place/'.$public->place->slug)->assertNotFound();
    }

    public function test_filters_match_same_item_and_or_within_dimension(): void
    {
        $visit = Visit::factory()->published()->create();
        $thai = Cuisine::create(['name' => 'Thai']);
        $italian = Cuisine::create(['name' => 'Italian']);
        $grill = CookingStyle::create(['name' => 'Bakar']);
        $boil = CookingStyle::create(['name' => 'Rebus']);
        $one = VisitItem::factory()->for($visit)->create();
        $one->cuisines()->attach($thai);
        $one->styles()->attach($boil);
        $two = VisitItem::factory()->for($visit)->create();
        $two->cuisines()->attach($italian);
        $two->styles()->attach($grill);
        foreach (['visit', 'place', 'food'] as $tab) {
            $this->assertCount(0, $this->results(['tab' => $tab, 'cuisine' => 'thai', 'style' => 'bakar']));
            $this->assertCount($tab === 'food' ? 2 : 1, $this->results(['tab' => $tab, 'cuisine' => ['thai', 'italian'], 'style' => ['bakar', 'rebus']]));
        }
    }

    public function test_category_and_cuisine_descendants_and_known_ingredients(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $child = Category::create(['name' => 'Mie', 'parent_id' => $category->id]);
        $type = FoodType::create(['name' => 'Mie Ayam', 'category_id' => $child->id]);
        $region = Cuisine::create(['name' => 'Asia', 'classification' => 'region']);
        $thai = Cuisine::create(['name' => 'Thai', 'parent_id' => $region->id]);
        $ingredient = Ingredient::create(['name' => 'Ayam']);
        $item = VisitItem::factory()->create(['visit_id' => Visit::factory()->published(), 'food_type_id' => $type->id]);
        $item->cuisines()->attach($thai);
        $item->ingredients()->attach($ingredient, ['knowledge_source' => 'Keterangan penjual']);
        $this->assertSame([$item->id], $this->results(['tab' => 'food', 'category' => 'makanan', 'cuisine' => 'asia', 'ingredient' => 'ayam'])->modelKeys());
        $options = app(PublicOptions::class)->all();
        $this->assertContains('makanan', array_column($options['category'], 'value'));
        $this->assertContains('asia', array_column($options['cuisine'], 'value'));
    }

    public function test_allergen_exclusion_contains_may_contain_unknown_and_group(): void
    {
        $this->seed(AllergenSeeder::class);
        $milk = Allergen::where('code', 'milk')->first();
        $wheat = Allergen::where('code', 'wheat')->first();
        $visit = Visit::factory()->published()->create();
        $one = VisitItem::factory()->for($visit)->create(['menu_name' => 'Susu tercatat']);
        $two = VisitItem::factory()->for($visit)->create(['menu_name' => 'Potensi susu']);
        $unknown = VisitItem::factory()->for($visit)->create(['menu_name' => 'Informasi belum ada']);
        $one->allergens()->attach($milk, ['exposure_status' => 'contains', 'evidence_source' => 'Label menu']);
        $two->allergens()->attach($milk, ['exposure_status' => 'may_contain', 'evidence_source' => 'Penjual']);
        $this->assertSame([$unknown->id], $this->results(['tab' => 'food', 'avoid_allergen' => ['milk']])->modelKeys());
        $this->get('/explore?tab=food&avoid_allergen=milk')->assertOk()->assertSee('Informasi belum tersedia')->assertSee(config('foodie.allergen_disclaimer'))->assertDontSee('Susu tercatat')->assertDontSee('Potensi susu');
        $unknown->allergens()->attach($wheat, ['exposure_status' => 'contains', 'evidence_source' => 'Label']);
        $this->assertCount(0, $this->results(['tab' => 'food', 'avoid_allergen' => ['milk', 'cereals_gluten']]));
        $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Label menu')->assertSee('Berpotensi mengandung');
    }

    public function test_prices_ratings_sort_and_tab_reset_have_correct_semantics(): void
    {
        $a = Visit::factory()->published()->create(['total_spent_idr' => 60000, 'service_rating' => 3.5]);
        $b = Visit::factory()->published()->create(['total_spent_idr' => null]);
        $food = VisitItem::factory()->for($a)->create(['unit_price_idr' => 25000, 'taste_rating' => 4]);
        VisitItem::factory()->for($b)->create();
        $this->assertCount(0, $this->results(['tab' => 'visit', 'price_max' => 30000]));
        $this->assertSame([$food->id], $this->results(['tab' => 'food', 'price_max' => 30000])->modelKeys());
        $this->assertCount(2, $this->results(['tab' => 'place', 'price_max' => 30000]));
        $this->assertSame($a->id, $this->results(['tab' => 'visit', 'sort' => 'price_high'])->first()->id);
        $this->assertSame($a->id, $this->results(['tab' => 'visit', 'sort' => 'rating_low'])->first()->id);
        $this->assertCount(0, $this->results(['tab' => 'visit', 'rating_min' => 4]));
        $this->assertCount(1, $this->results(['tab' => 'food', 'rating_min' => 4]));
        $state = new ExploreState(['tab' => 'food', 'price_max' => 30000, 'city' => 'semarang', 'view' => 'list', 'page' => 2]);
        parse_str(parse_url($state->tabUrl('visit'), PHP_URL_QUERY), $query);
        $this->assertArrayNotHasKey('price_max', $query);
        $this->assertArrayNotHasKey('page', $query);
        $this->assertSame('list', $query['view']);
        $this->assertSame('1', $query['price_reset']);
    }

    public function test_invalid_query_sort_and_stable_pagination(): void
    {
        Visit::factory()->published()->count(20)->create(['visited_at' => '2026-11-04 01:00:00']);
        $this->get('/explore?sort=title%20desc;%20DROP%20TABLE%20visit&tab=bad&q[]=x&price_min[]=2')->assertOk();
        $results = $this->results();
        $this->assertSame(Visit::max('id'), $results->first()->id);
        $response = $this->get('/explore?view=list');
        $response->assertOk()->assertSee('page=2')->assertSee('view=list');
        $this->assertSame(20, Visit::count());
    }

    public function test_published_slug_redirect_respects_current_eligibility_and_private_slugs_stay_private(): void
    {
        $visit = Visit::factory()->published()->create();
        $old = $visit->slug;
        DB::transaction(function () use ($visit) {
            app(Slugger::class)->assign($visit, 'slug-baru');
            $visit->save();
        });
        $this->get('/visit/'.$old)->assertStatus(301)->assertRedirect('/visit/slug-baru');
        $visit->update(['status' => 'private']);
        $this->get('/visit/'.$old)->assertNotFound();
        $this->get('/visit/slug-baru')->assertNotFound();
        $private = Visit::factory()->create();
        $secret = $private->slug;
        app(Slugger::class)->assign($private, 'sekarang-publik');
        $private->status = 'published';
        $private->save();
        $this->get('/visit/'.$secret)->assertNotFound();
    }
}
