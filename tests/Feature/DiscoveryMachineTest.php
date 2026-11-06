<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoodType;
use App\Models\RecommendationLabel;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\DiscoveryMachine;
use App\Support\ExploreState;
use App\Support\PublicOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DiscoveryMachineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['discovery.presets' => []]);
    }

    public function test_default_is_all_published_visits_and_direct_type_url_keeps_memory_and_results_coherent(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $noodle = FoodType::create(['name' => 'Mie', 'category_id' => $category->id]);
        $rice = FoodType::create(['name' => 'Nasi', 'category_id' => $category->id]);
        $older = Visit::factory()->published()->create(['title' => 'Catatan mie', 'visited_at' => '2026-10-01']);
        $newer = Visit::factory()->published()->create(['title' => 'Catatan nasi', 'visited_at' => '2026-11-01']);
        $private = Visit::factory()->private()->create(['title' => 'RAHASIA-PRIVATE-MESIN']);
        VisitItem::factory()->for($older)->create(['food_type_id' => $noodle->id]);
        VisitItem::factory()->for($newer)->create(['food_type_id' => $rice->id]);
        VisitItem::factory()->for($private)->create(['food_type_id' => $noodle->id]);

        $this->get('/')->assertOk()->assertSee('data-home-archive', false)->assertSee('data-total="2"', false);
        $this->get('/explore')->assertOk()->assertSee('The Curiosity Index')->assertDontSee('data-machine', false)
            ->assertSeeInOrder(['Catatan nasi', 'Catatan mie'])->assertDontSee('RAHASIA-PRIVATE-MESIN');
        $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?food_type%5B0%5D=mie')->assertOk()
            ->assertSee('data-selected-label="Mie"', false)->assertDontSee('RAHASIA-PRIVATE-MESIN');
        $this->assertSame(1, $response->viewData('memory')['count']);
        $this->assertStringContainsString('food_type%5B0%5D=mie', $response->getContent());
        $response->assertDontSee('<!doctype html>', false)->assertDontSee('data-filter-drawer', false);

    }

    public function test_unpublished_only_types_never_enter_presets_or_fragment(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $hidden = FoodType::create(['name' => 'RAHASIA-JENIS', 'category_id' => $category->id]);
        VisitItem::factory()->for(Visit::factory()->private())->create(['food_type_id' => $hidden->id]);

        $this->withHeader('X-Archive-Fragment', '1')->get('/explore')->assertOk()->assertDontSee('RAHASIA-JENIS');
        $this->withHeader('X-Explore-Fragment', '1')->get('/explore')->assertOk()->assertDontSee('RAHASIA-JENIS');
        $this->withHeader('X-Archive-Fragment', '1')->get('/explore?food_type%5B0%5D='.$hidden->slug)->assertOk()->assertSee('0 kunjungan')->assertDontSee('RAHASIA-JENIS');
    }

    public function test_slots_use_public_options_and_leave_the_full_taxonomy_in_the_console(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $visit = Visit::factory()->published()->create();
        for ($index = 0; $index < 12; $index++) {
            $type = FoodType::create(['name' => 'Jenis '.$index, 'category_id' => $category->id]);
            VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id]);
        }
        $options = app(PublicOptions::class)->all();
        $slots = app(DiscoveryMachine::class)->slots(new ExploreState([]), $options);
        $this->assertCount(6, $slots);
        $this->assertSame('latest', $slots[0]['id']);
        $this->assertNotContains('console', array_column($slots, 'id'));
        $this->assertCount(12, $options['food_type']);
        $this->assertCount(2, array_filter($slots, fn ($slot) => str_starts_with($slot['id'], 'food_type-')));
    }

    public function test_configured_combined_preset_uses_the_same_query_and_rejects_unpublished_terms(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $type = FoodType::create(['name' => 'Soto', 'category_id' => $category->id]);
        $visit = Visit::factory()->published()->create(['title' => 'Soto Semarang']);
        $visit->place->update(['city_name' => 'Semarang']);
        VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id]);
        config(['discovery.presets' => [
            ['id' => 'soto-semarang', 'label' => 'Soto di Semarang', 'filters' => ['food_type' => ['soto'], 'city' => ['semarang']]],
            ['id' => 'secret', 'label' => 'RAHASIA-PRESET', 'filters' => ['food_type' => ['private-only']]],
        ]]);
        $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?food_type=soto&city=semarang')->assertOk()->assertDontSee('RAHASIA-PRESET');
        $this->assertSame('soto-semarang', $response->viewData('memory')['slot']);
        $this->assertSame(1, $response->viewData('memory')['count']);
        $this->assertSame('Soto di Semarang', $response->viewData('memory')['label']);
        $this->withHeader('X-Archive-Fragment', '1')->get('/explore?food_type=soto&city=jakarta')->assertOk()->assertSee('Belum ada catatan yang cocok.');
    }

    public function test_memory_uses_matching_item_photos_and_never_substitutes_an_unrelated_cover(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $tea = FoodType::create(['name' => 'Teh', 'category_id' => $category->id]);
        $salad = FoodType::create(['name' => 'Salad', 'category_id' => $category->id]);
        $visit = Visit::factory()->published()->create();
        $teaItem = VisitItem::factory()->for($visit)->create(['food_type_id' => $tea->id, 'menu_name' => 'Teh yang dicoba']);
        $saladItem = VisitItem::factory()->for($visit)->create(['food_type_id' => $salad->id, 'menu_name' => 'Salad yang dicoba']);
        $photo = function ($owner) {
            $media = $owner->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
            foreach (['thumbnail', 'card'] as $variant) {
                $media->variants()->create(['variant' => $variant, 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);
            }

            return $media;
        };
        $teaPhoto = $photo($visit);
        $saladPhoto = $photo($visit);
        $teaItem->media()->attach($teaPhoto);
        $saladItem->media()->attach($saladPhoto);
        $visit->update(['cover_media_id' => $saladPhoto->id]);
        $privateVisit = Visit::factory()->private()->create();
        $privatePhoto = $photo($privateVisit);
        VisitItem::factory()->for($privateVisit)->create(['food_type_id' => $tea->id])->media()->attach($privatePhoto);

        foreach (['visit', 'food', 'place'] as $tab) {
            $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab='.$tab.'&food_type=teh')->assertOk();
            $this->assertSame(url('media/'.$teaPhoto->uuid.'/card'), $response->viewData('memory')['image']);
            $this->assertSame(match ($tab) {
                'food' => 'Teh yang dicoba', 'place' => $visit->place->name, default => $visit->display_title
            }, $response->viewData('memory')['title']);
            $this->assertSame(1, $response->viewData('memory')['count']);
            $other = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab='.$tab.'&food_type=salad')->assertOk();
            $this->assertSame(url('media/'.$saladPhoto->uuid.'/card'), $other->viewData('memory')['image']);
            $teaItem->media()->detach();
            $fallback = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab='.$tab.'&food_type=teh')->assertOk();
            $this->assertNull($fallback->viewData('memory')['image']);
            $teaItem->media()->attach($teaPhoto);
        }
    }

    public function test_place_memory_uses_a_visit_with_the_selected_recommendation(): void
    {
        $older = Visit::factory()->published()->create(['visited_at' => '2026-10-01', 'title' => 'Kunjungan berlabel', 'service_rating' => 2]);
        $newer = Visit::factory()->published()->create(['place_id' => $older->place_id, 'visited_at' => '2026-11-01', 'title' => 'Kunjungan tanpa label', 'service_rating' => 5]);
        $label = RecommendationLabel::create(['name' => 'Ingin kembali']);
        $older->recommendations()->attach($label);
        $this->assertEquals(3.5, $older->place()->withRating()->first()->rating_score);
        foreach ([$older, $newer] as $visit) {
            $media = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
            $media->variants()->create(['variant' => 'card', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);
            $visit->update(['cover_media_id' => $media->id]);
        }
        $memory = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab=place&rating_min=3&recommendation='.$label->slug)->assertOk()->viewData('memory');
        $this->assertSame(url('place/'.$older->place->slug), $memory['href']);
        $this->assertSame(url('media/'.$older->fresh()->coverMedia->uuid.'/card'), $memory['image']);
        $this->assertSame(1, $memory['count']);
    }

    public function test_six_configured_collections_have_real_matching_photos_and_no_inline_geometry(): void
    {
        config(['discovery.presets' => (require base_path('config/discovery.php'))['presets']]);
        $visit = Visit::factory()->published()->create();
        $visit->place->update(['city_name' => 'Bandung']);
        $category = Category::create(['name' => 'Makanan']);
        $photo = $this->archivePhoto($visit);
        foreach (['Kopi', 'Pastry', 'Mi & ramen', 'Pencuci mulut'] as $name) {
            $type = FoodType::create(['name' => $name, 'category_id' => $category->id]);
            VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id])->media()->attach($photo);
        }
        $visit->recommendations()->attach(RecommendationLabel::create(['name' => 'Favorit pribadi']));
        $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore')->assertOk();
        $slots = $response->viewData('slots');
        $this->assertSame(['coffee', 'pastry', 'noodles', 'bandung', 'personal', 'sweet'], array_column($slots, 'id'));
        $this->assertSame(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'], array_column($slots, 'code'));
        foreach ($slots as $slot) {
            $this->assertSame(url('media/'.$photo->uuid.'/card'), $slot['image']);
        }
        $response->assertDontSee('style="', false);
    }

    public function test_gallery_photos_survive_missing_cover_and_an_unphotographed_latest_place_visit(): void
    {
        $older = Visit::factory()->published()->create(['visited_at' => '2026-10-01']);
        $photo = $this->archivePhoto($older);
        Visit::factory()->published()->create(['place_id' => $older->place_id, 'visited_at' => '2026-11-01']);
        $private = Visit::factory()->private()->create(['place_id' => $older->place_id, 'visited_at' => '2026-11-02']);
        $secret = $this->archivePhoto($private);
        foreach (['visit', 'place'] as $tab) {
            $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab='.$tab)->assertOk();
            $response->assertSee('media/'.$photo->uuid.'/card', false)->assertDontSee($secret->uuid);
            $this->assertSame(url('media/'.$photo->uuid.'/card'), $response->viewData('memory')['image']);
        }
    }

    public function test_surprise_draw_uses_entire_filtered_set_and_avoids_previous_memory(): void
    {
        $one = Visit::factory()->published()->create(['title' => 'Kenangan terbuka satu']);
        $two = Visit::factory()->published()->create(['title' => 'Kenangan terbuka dua', 'place_id' => $one->place_id]);
        $private = Visit::factory()->private()->create(['title' => 'RAHASIA-DRAW', 'place_id' => $one->place_id]);
        $city = $one->place->city_slug;
        $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore/memory?city='.$city.'&page=999')->assertOk()->assertDontSee('RAHASIA-DRAW');
        $memory = $response->viewData('memory');
        $this->assertContains($memory['object_id'], [$one->id, $two->id]);
        $this->assertSame(2, $memory['count']);
        $next = $this->withHeader('X-Archive-Fragment', '1')->get('/explore/memory?city='.$city.'&exclude='.$memory['object_id'])->assertOk()->viewData('memory');
        $this->assertNotSame($memory['object_id'], $next['object_id']);
        $this->assertNotSame($private->id, $next['object_id']);
        $this->withHeader('X-Archive-Fragment', '1')->get('/explore/memory?q=no-match-ever')->assertOk()->assertSee('data-memory-count="0"', false);
        $one->update(['status' => 'private']);
        $single = $this->withHeader('X-Archive-Fragment', '1')->get('/explore/memory?city='.$city.'&exclude='.$two->id)->assertOk()->viewData('memory');
        $this->assertSame($two->id, $single['object_id']);
        $this->assertSame(1, $single['count']);
    }

    public function test_surprise_destinations_follow_the_browsing_mode_and_initial_screen_is_an_overview(): void
    {
        $visit = Visit::factory()->published()->create(['title' => 'Judul kenangan tersembunyi']);
        $item = VisitItem::factory()->for($visit)->create(['menu_name' => 'Hidangan kenangan tersembunyi']);
        foreach (['visit', 'food', 'place'] as $tab) {
            $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?tab='.$tab)->assertOk();
            preg_match('/<article id="memory-showcase".*?<\/article>/s', $response->getContent(), $screen);
            $this->assertStringNotContainsString($visit->title, $screen[0]);
            $this->assertStringNotContainsString($item->menu_name, $screen[0]);
            $this->assertStringContainsString('data-screen-kind="collection"', $screen[0]);
            $memory = $this->withHeader('X-Archive-Fragment', '1')->get('/explore/memory?tab='.$tab)->assertOk()->viewData('memory');
            $this->assertSame(match ($tab) {
                'food' => url('visit/'.$visit->slug).'#hidangan-'.$item->id,
                'place' => url('place/'.$visit->place->slug),
                default => url('visit/'.$visit->slug),
            }, $memory['href']);
        }
    }

    public function test_preset_reset_subtracts_only_its_contribution_and_retains_manual_filters(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $type = FoodType::create(['name' => 'Soto', 'category_id' => $category->id]);
        $visit = Visit::factory()->published()->create();
        $visit->place->update(['city_name' => 'Semarang']);
        VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id]);
        $response = $this->withHeader('X-Archive-Fragment', '1')->get('/explore?q=Soto&city=semarang&view=list&food_type=nasi')->assertOk();
        $slot = collect($response->viewData('slots'))->firstWhere('id', 'food_type-soto');
        parse_str(parse_url($slot['url'], PHP_URL_QUERY), $params);
        $this->assertSame('Soto', $params['q']);
        $this->assertSame(['semarang'], $params['city']);
        $this->assertSame('list', $params['view']);
        $selected = $this->withHeader('X-Archive-Fragment', '1')->get($slot['url'])->assertOk();
        parse_str(parse_url($selected->viewData('resetUrl'), PHP_URL_QUERY), $reset);
        $this->assertSame(['nasi'], $reset['food_type']);
        $this->assertArrayNotHasKey('collection', $reset);
        $this->assertSame('Soto', $reset['q']);
        $this->assertSame(['semarang'], $reset['city']);
    }

    public function test_active_mode_link_retains_its_price_and_sort_while_mode_change_resets_price_context(): void
    {
        $state = new ExploreState(['tab' => 'food', 'price_min' => 5000, 'sort' => 'price_high']);
        parse_str(parse_url($state->tabUrl('food'), PHP_URL_QUERY), $same);
        $this->assertSame('5000', $same['price_min']);
        $this->assertSame('price_high', $same['sort']);
        parse_str(parse_url($state->tabUrl('visit'), PHP_URL_QUERY), $different);
        $this->assertArrayNotHasKey('price_min', $different);
        $this->assertSame('1', $different['price_reset']);
    }

    private function archivePhoto(Visit $visit)
    {
        $media = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
        $media->variants()->create(['variant' => 'card', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);

        return $media;
    }
}
