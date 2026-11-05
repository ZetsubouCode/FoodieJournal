<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoodType;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\DiscoveryMachine;
use App\Support\ExploreState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryMachineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_default_is_all_published_visits_and_direct_type_url_keeps_projection_and_results_coherent(): void
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

        $this->get('/explore')->assertOk()->assertSee('data-selected-index="0"', false)
            ->assertSee('Semua rasa')->assertSeeInOrder(['Catatan nasi', 'Catatan mie'])
            ->assertDontSee('RAHASIA-PRIVATE-MESIN');
        $response = $this->get('/explore?food_type%5B0%5D=mie')->assertOk()
            ->assertSee('data-selected-label="Mie"', false)->assertSee('1 kunjungan pada hasil ini')
            ->assertSee('Catatan mie')->assertDontSee('Catatan nasi')->assertDontSee('RAHASIA-PRIVATE-MESIN');
        $this->assertStringContainsString('food_type%5B0%5D=mie', $response->getContent());
        $this->withHeader('X-Explore-Fragment', '1')->get('/explore?food_type%5B0%5D=mie')
            ->assertOk()->assertSee('data-selected-label="Mie"', false)->assertDontSee('<!doctype html>', false);
    }

    public function test_unpublished_only_types_never_enter_wheel_or_fragment(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $hidden = FoodType::create(['name' => 'RAHASIA-JENIS', 'category_id' => $category->id]);
        VisitItem::factory()->for(Visit::factory()->private())->create(['food_type_id' => $hidden->id]);

        $this->get('/explore')->assertOk()->assertDontSee('RAHASIA-JENIS');
        $this->withHeader('X-Explore-Fragment', '1')->get('/explore')->assertOk()->assertDontSee('RAHASIA-JENIS');
        $this->get('/explore?food_type%5B0%5D='.$hidden->slug)->assertOk()->assertSee('0 kunjungan pada hasil ini')->assertDontSee('RAHASIA-JENIS');
    }

    public function test_sparse_wheel_spaces_real_choices_and_skips_fabricated_types(): void
    {
        $category = Category::create(['name' => 'Makanan']);
        $type = FoodType::create(['name' => 'Satu jenis nyata', 'category_id' => $category->id]);
        VisitItem::factory()->for(Visit::factory()->published())->create(['food_type_id' => $type->id]);

        $slots = app(DiscoveryMachine::class)->wheel(new ExploreState([]));
        $this->assertSame('all', $slots[0]['kind']);
        $this->assertSame('type', $slots[3]['kind']);
        $this->assertSame($type->slug, $slots[3]['slug']);
        $this->assertCount(4, array_filter($slots, fn ($slot) => $slot['kind'] === 'empty'));
    }
}
