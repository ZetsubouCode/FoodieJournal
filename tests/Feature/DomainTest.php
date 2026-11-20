<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Rules\HalfRating;
use App\Support\Presentation;
use Database\Seeders\AllergenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_food_score_excludes_nulls_and_rounds_only_for_display(): void
    {
        $visit = Visit::factory()->published()->create();
        $this->assertNull($visit->fresh()->food_score);
        VisitItem::factory()->for($visit)->create(['food_rating' => 4.5]);
        $this->assertSame(4.5, $visit->fresh()->food_score);
        $second = VisitItem::factory()->for($visit)->create(['food_rating' => 3.5]);
        VisitItem::factory()->for($visit)->create();

        $this->assertSame(4.0, Visit::withFoodScore()->find($visit->id)->food_score);
        $second->update(['food_rating' => 4.0]);
        foreach ([$visit->fresh(), $visit->fresh('items'), Visit::withFoodScore()->find($visit->id)] as $loaded) {
            $this->assertSame(4.25, $loaded->food_score);
            $this->assertSame(2, $loaded->rated_item_count);
            $this->assertSame('4,3', Presentation::rating($loaded->food_score));
        }
        $visit->items()->update(['food_rating' => null]);
        $unrated = Visit::withFoodScore()->find($visit->id);
        $this->assertNull($unrated->food_score);
        $this->assertSame(0, $unrated->rated_item_count);
    }

    public function test_place_score_weights_rated_item_occurrences_and_revokes_hidden_visits(): void
    {
        $first = Visit::factory()->published()->create();
        $placeId = $first->place_id;
        $repeat = Visit::factory()->published()->create(['place_id' => $placeId]);
        VisitItem::factory()->for($first)->create(['food_rating' => 5, 'menu_name' => 'Soto']);
        VisitItem::factory()->for($repeat)->count(3)->create(['food_rating' => 3, 'menu_name' => 'Soto', 'quantity' => 2]);
        VisitItem::factory()->for($repeat)->create();
        foreach (['draft', 'private', 'published'] as $status) {
            $hidden = Visit::factory()->create(['place_id' => $placeId, 'status' => $status]);
            VisitItem::factory()->for($hidden)->create(['food_rating' => 1]);
            if ($status === 'published') {
                $hidden->delete();
            }
        }

        $place = Place::withFoodScore()->find($placeId);
        $this->assertSame(3.5, $place->food_score);
        $this->assertSame(4, $place->rated_item_count);
        $this->assertSame(2, $place->rated_visit_count);

        $repeat->update(['status' => 'private']);
        $this->assertSame(5.0, Place::withFoodScore()->find($placeId)->food_score);
        $place->delete();
        $deleted = Place::withTrashed()->withFoodScore()->find($placeId);
        $this->assertNull($deleted->food_score);
        $this->assertSame(0, $deleted->rated_item_count);
        $this->assertSame(0, $deleted->rated_visit_count);
    }

    public function test_rating_validation_rejects_out_of_range_and_fractional_values(): void
    {
        foreach ([0, .5, 5.5, 4.2, 'x', '4.50x'] as $value) {
            $this->assertTrue(Validator::make(['rating' => $value], ['rating' => [new HalfRating]])->fails());
        }
        foreach ([1, 1.5, '4.50', 5, null] as $value) {
            $this->assertTrue(Validator::make(['rating' => $value], ['rating' => ['nullable', new HalfRating]])->passes());
        }
    }

    public function test_singular_schema_seeds_and_nullable_prices(): void
    {
        $tables = Schema::getTableListing();
        foreach (['users', 'visits', 'visit_items', 'sessions', 'jobs', 'migrations', 'cache_locks'] as $table) {
            $this->assertNotContains($table, $tables);
        }
        $this->seed(AllergenSeeder::class);
        $this->seed(AllergenSeeder::class);
        $this->assertSame(17, DB::table('allergen')->count());
        $item = VisitItem::factory()->create(['unit_price_idr' => 0]);
        $this->assertSame(0, $item->unit_price_idr);
        $this->assertNull($item->visit->total_spent_idr);
    }

    public function test_visibility_excludes_deleted_place_and_unpublished(): void
    {
        $visit = Visit::factory()->published()->create();
        Visit::factory()->private()->create();
        Visit::factory()->create();
        $this->assertSame(1, Visit::publishedVisible()->count());
        $visit->place->delete();
        $this->assertSame(0, Visit::publishedVisible()->count());
        $this->assertSame(0, Place::publishedVisible()->count());
    }

    public function test_slug_collision_and_existing_title_does_not_rename(): void
    {
        $one = Place::factory()->create(['name' => 'Warung Sama']);
        $two = Place::factory()->create(['name' => 'Warung Sama']);
        $this->assertSame('warung-sama', $one->slug);
        $this->assertSame('warung-sama-2', $two->slug);
        $one->update(['name' => 'Nama lain']);
        $this->assertSame('warung-sama', $one->fresh()->slug);
    }
}
