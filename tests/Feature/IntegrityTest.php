<?php

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\ExploreQuery;
use App\Support\ExploreState;
use Database\Seeders\AllergenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_taxonomy_and_allergen_evidence_are_allowed_across_items(): void
    {
        $this->seed(AllergenSeeder::class);
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();
        $this->actingAs($user);
        $visit = Visit::factory()->create(['user_id' => $user->id]);
        $cuisine = Cuisine::create(['name' => 'Thai']);
        $style = CookingStyle::create(['name' => 'Rebus']);
        $milk = Allergen::where('code', 'milk')->first();
        $item = ['menu_name' => 'Hidangan', 'cuisines' => [$cuisine->id], 'styles' => [$style->id], 'allergens' => [['id' => $milk->id, 'exposure_status' => 'may_contain', 'evidence_source' => 'Keterangan penjual']]];
        $this->put('/admin/visit/'.$visit->id, ['place_id' => $visit->place_id, 'editor' => 1, 'version' => 1, 'intent' => 'draft', 'items' => [$item, $item]])->assertSessionHasNoErrors();
        $this->assertSame(2, $visit->items()->count());
        $this->assertSame(2, DB::table('visit_item_allergen')->count());
        $this->assertNotNull($visit->fresh()->visited_at);
    }

    public function test_historical_names_prices_and_ingredient_reference_do_not_infer_exposure(): void
    {
        $this->seed(AllergenSeeder::class);
        $type = FoodType::create(['name' => 'Mie ayam']);
        $visit = Visit::factory()->published()->create();
        $item = VisitItem::factory()->for($visit)->create(['food_type_id' => $type->id, 'menu_name' => 'Mie spesial lama', 'unit_price_idr' => 20000]);
        VisitItem::factory()->create(['visit_id' => Visit::factory()->published()->create(['place_id' => $visit->place_id])->id, 'food_type_id' => $type->id, 'menu_name' => 'Mie spesial baru', 'unit_price_idr' => 25000]);
        $type->update(['name' => 'Jenis direvisi']);
        $this->assertSame('Mie spesial lama', $item->fresh()->menu_name);
        $this->assertSame(20000, $item->fresh()->unit_price_idr);
        $ingredient = Ingredient::create(['name' => 'Bahan contoh']);
        $allergen = Allergen::where('code', 'wheat')->first();
        $ingredient->allergens()->attach($allergen, ['relation_status' => 'may_contain', 'notes' => 'Referensi bantuan, bukan bukti hidangan']);
        $item->ingredients()->attach($ingredient);
        $results = app(ExploreQuery::class)->build(new ExploreState(['tab' => 'food', 'avoid_allergen' => 'wheat']))->get();
        $this->assertContains($item->id, $results->modelKeys());
        $this->assertCount(0, $item->allergens);
    }

    public function test_public_query_count_does_not_grow_with_card_count(): void
    {
        $this->withoutVite();
        Visit::factory()->published()->create();
        DB::enableQueryLog();
        $this->get('/explore')->assertOk();
        $one = count(DB::getQueryLog());
        DB::disableQueryLog();
        Visit::factory()->published()->count(17)->create();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get('/explore')->assertOk();
        $many = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame($one, $many, 'Query count should remain bounded for one versus eighteen visit cards.');
        file_put_contents(base_path('tools/.runtime/query-count.json'), json_encode(['one_card' => $one, 'eighteen_cards' => $many]));
    }

    public function test_malformed_nested_input_is_validation_error_and_old_items_are_restored(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();
        $this->actingAs($user);
        $visit = Visit::factory()->create(['user_id' => $user->id]);
        $base = ['place_id' => $visit->place_id, 'version' => 1, 'editor' => 1, 'intent' => 'draft'];
        $this->putJson('/admin/visit/'.$visit->id, [...$base, 'items' => 'malformed'])->assertUnprocessable();
        $this->putJson('/admin/visit/'.$visit->id, [...$base, 'experience' => str_repeat('🍜', 20000)])->assertUnprocessable()->assertJsonValidationErrors('experience');
        $url = '/admin/visit/'.$visit->id.'/edit';
        $this->from($url)->put('/admin/visit/'.$visit->id, [...$base, 'items' => [['menu_name' => 'Nama belum tersimpan', 'taste_rating' => 4.2, 'unit_price_idr' => -1]]])->assertSessionHasErrors(['items.0.taste_rating', 'items.0.unit_price_idr']);
        $this->get($url)->assertOk()->assertSee('Nama belum tersimpan');
        $this->assertSame(0, $visit->items()->count());
    }
}
