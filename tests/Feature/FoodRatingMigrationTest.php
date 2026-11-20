<?php

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Place;
use App\Models\Visit;
use App\Models\VisitItem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoodRatingMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_upgrade_guards_legacy_notes_then_preserves_food_values_history_and_keys(): void
    {
        $rated = Visit::factory()->published()->create(['total_spent_idr' => 42000]);
        $legacyOnly = Visit::factory()->published()->create(['place_id' => $rated->place_id]);
        $item = VisitItem::factory()->for($rated)->create(['food_rating' => 4.5, 'unit_price_idr' => 12000, 'notes' => 'Bumbu meresap.']);
        $unrated = VisitItem::factory()->for($legacyOnly)->create();
        $cuisine = Cuisine::create(['name' => 'Jawa']);
        $item->cuisines()->attach($cuisine);
        $tables = Schema::getTableListing();
        $itemForeignKeys = Schema::getForeignKeys('visit_item');
        $visitForeignKeys = Schema::getForeignKeys('visit');

        $migration = require database_path('migrations/2026_11_19_000001_food_only_ratings_and_visit_observations.php');
        $migration->down();
        $this->assertEquals(4.5, DB::table('visit_item')->where('id', $item->id)->value('taste_rating'));
        DB::table('visit')->where('id', $legacyOnly->id)->update(['service_rating' => 5, 'service_review' => 'Catatan yang perlu ditinjau.']);

        $approval = getenv('FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP');
        try {
            putenv('FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP');
            try {
                $migration->up();
                $this->fail('Cleanup must stop for unreviewed legacy notes.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('Legacy Visit notes need owner review', $exception->getMessage());
            }
            $this->assertTrue(Schema::hasColumn('visit_item', 'taste_rating'));
            $this->assertFalse(Schema::hasColumn('visit', 'pros'));
            $this->assertSame('Catatan yang perlu ditinjau.', DB::table('visit')->where('id', $legacyOnly->id)->value('service_review'));
        } finally {
            // Return the fixture to the migrated schema before DatabaseMigrations rolls it back.
            putenv('FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP=1');
            try {
                $migration->up();
            } finally {
                putenv($approval === false ? 'FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP' : 'FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP='.$approval);
            }
        }

        $this->assertEqualsCanonicalizing($tables, Schema::getTableListing());
        $this->assertEqualsCanonicalizing($itemForeignKeys, Schema::getForeignKeys('visit_item'));
        $this->assertEqualsCanonicalizing($visitForeignKeys, Schema::getForeignKeys('visit'));
        $visitColumns = array_column(Schema::getColumns('visit'), null, 'name');
        foreach (['pros', 'cons'] as $column) {
            $this->assertSame('text', $visitColumns[$column]['type_name']);
            $this->assertTrue($visitColumns[$column]['nullable']);
            $this->assertContains($visitColumns[$column]['default'], [null, 'NULL']);
        }
        foreach (['service', 'atmosphere', 'cleanliness', 'value'] as $field) {
            $this->assertArrayNotHasKey($field.'_rating', $visitColumns);
            $this->assertArrayNotHasKey($field.'_review', $visitColumns);
        }
        $this->assertTrue(Schema::hasColumn('visit_item', 'food_rating'));
        $this->assertFalse(Schema::hasColumn('visit_item', 'taste_rating'));
        $this->assertSame('4.5', $item->fresh()->food_rating);
        $this->assertNull($unrated->fresh()->food_rating);
        $this->assertSame(42000, $rated->fresh()->total_spent_idr);
        $this->assertSame(12000, $item->fresh()->unit_price_idr);
        $this->assertSame('Bumbu meresap.', $item->fresh()->notes);
        $this->assertSame($cuisine->id, $item->cuisines()->sole()->id);
        $this->assertNull($legacyOnly->fresh()->pros);
        $this->assertNull($legacyOnly->fresh()->cons);
        $this->assertNull($legacyOnly->fresh()->food_score);
        $place = Place::withFoodScore()->find($rated->place_id);
        $this->assertSame(4.5, $place->food_score);
        $this->assertSame(1, $place->rated_item_count);
        $this->assertSame(1, $place->rated_visit_count);
    }
}
