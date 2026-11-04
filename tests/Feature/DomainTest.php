<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Rules\HalfRating;
use Database\Seeders\AllergenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_precision_and_published_place_aggregate(): void
    {
        $visit = Visit::factory()->published()->create(['service_rating' => 3.5]);
        VisitItem::factory()->for($visit)->create(['taste_rating' => 4.5]);
        VisitItem::factory()->for($visit)->create(['taste_rating' => 3.5]);
        VisitItem::factory()->for($visit)->create();
        Visit::factory()->private()->create(['place_id' => $visit->place_id, 'service_rating' => 1]);
        Visit::factory()->published()->create(['place_id' => $visit->place_id]);
        $this->assertSame(3.75, $visit->fresh()->overall_rating);
        $this->assertEquals(3.75, Visit::withRating()->find($visit->id)->overall_rating);
        $place = Place::withRating()->find($visit->place_id);
        $this->assertEquals(3.75, $place->rating_score);
        $this->assertEquals(1, $place->rated_visit_count);
        $visit->update(['status' => 'private']);
        $this->assertNull(Place::withRating()->find($place->id)->rating_score);
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
