<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Place;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\MediaProcessor;
use App\Support\PublicOptions;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoJournalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_populates_public_and_admin_content_and_preserves_edits_on_rerun(): void
    {
        $this->withoutVite();
        Storage::fake('media');
        $owner = User::factory()->create(['is_admin' => true]);
        $credentials = $owner->only(['name', 'email', 'password']);
        $existing = Visit::factory()->published()->for($owner, 'user')->create(['title' => 'Catatan asli pemilik']);

        $this->seed(DemoJournalSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame($credentials, $owner->fresh()->only(['name', 'email', 'password']));
        $this->assertSame(22, Visit::where('slug', 'like', 'demo-%')->count());
        $this->assertSame(20, Visit::publishedVisible()->where('slug', 'like', 'demo-%')->count());
        $this->assertSame(12, Place::where('slug', 'like', 'demo-%')->count());
        $this->assertSame(66, VisitItem::count());
        $this->assertSame(42, Media::where('processing_status', 'ready')->count());
        $this->assertSame(126, DB::table('media_variant')->where('status', 'ready')->count());
        foreach (Media::with('variants')->get() as $media) {
            $this->assertTrue(Storage::disk('media')->exists($media->original_path));
            $this->assertTrue(Storage::disk('media')->exists($media->uuid.'/viewer'));
            $this->assertSame($media->sha256, hash('sha256', Storage::disk('media')->get($media->original_path)));
            foreach ($media->variants as $variant) {
                $this->assertTrue(Storage::disk('media')->exists($variant->path));
            }
            $this->assertSame($media->visit_id, $media->items()->sole()->visit_id);
        }
        foreach (app(PublicOptions::class)->all() as $dimension => $options) {
            $this->assertNotEmpty($options, $dimension.' needs populated demo filter options');
        }

        $this->get('/')->assertOk()->assertSee('Bara, obrolan, dan makan malam yang pelan');
        foreach (['visit', 'food', 'place'] as $tab) {
            $this->get('/explore?tab='.$tab)->assertOk()->assertDontSee('Draft:')->assertDontSee('Privat:');
        }
        $this->get('/explore')->assertSee('page=2');
        $this->getJson('/explore/count?city=bandung')->assertJson(['total' => 4]);
        foreach (Visit::publishedVisible()->where('slug', 'like', 'demo-%')->get() as $visit) {
            $this->get('/visit/'.$visit->slug)->assertOk()->assertSee('Catatan demo untuk pratinjau situs.');
        }
        foreach (Place::where('slug', 'like', 'demo-%')->get() as $place) {
            $this->get('/place/'.$place->slug)->assertOk();
        }
        $hidden = Visit::where('status', 'private')->sole();
        $this->get('/visit/'.$hidden->slug)->assertNotFound();
        $this->get('/media/'.$hidden->media()->first()->uuid.'/card')->assertNotFound();
        $this->actingAs($owner)->get('/admin')->assertOk();
        $this->get('/admin/visit')->assertOk();
        $this->get('/admin/visit/'.$hidden->id.'/edit')->assertOk()->assertSee($hidden->title);

        $edited = Visit::where('slug', 'demo-bara-di-ujung-hari')->sole();
        $edited->update(['title' => 'Judul yang saya ubah', 'slug' => 'judul-baru', 'status' => 'draft']);
        $deleted = Visit::where('slug', 'demo-semangkuk-hangat-blok-m')->sole();
        $deleted->delete();
        $counts = $this->counts();
        $files = Storage::disk('media')->allFiles();

        $this->seed(DemoJournalSeeder::class);

        $this->assertSame($counts, $this->counts());
        $this->assertSame($files, Storage::disk('media')->allFiles());
        $this->assertSame('Judul yang saya ubah', $edited->fresh()->title);
        $this->assertSame('draft', $edited->fresh()->status);
        $this->assertTrue($deleted->fresh()->trashed());
        $this->assertSame('Catatan asli pemilik', $existing->fresh()->title);
    }

    public function test_default_seed_keeps_the_journal_empty(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('allergen', 17);
        $this->assertDatabaseCount('visit', 0);
        $this->assertDatabaseCount('user', 0);
    }

    public function test_demo_requires_an_existing_owner(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('php artisan foodie:admin');
        $this->seed(DemoJournalSeeder::class);
    }

    public function test_demo_refuses_production_even_when_forced(): void
    {
        $this->app['env'] = 'production';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('local and testing');
        $this->artisan('db:seed', ['--class' => DemoJournalSeeder::class, '--force' => true]);
    }

    public function test_processing_failure_rolls_back_the_visit_and_removes_its_new_files(): void
    {
        Storage::fake('media');
        User::factory()->create(['is_admin' => true]);
        $this->mock(MediaProcessor::class)->shouldReceive('process')->once()->andReturn(false);
        try {
            $this->seed(DemoJournalSeeder::class);
            $this->fail('A failed demo image must stop seeding.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('Could not process demo photo', $error->getMessage());
        }
        $this->assertDatabaseCount('visit', 0);
        $this->assertDatabaseCount('place', 0);
        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('media')->allFiles());
    }

    private function counts(): array
    {
        return collect(['user', 'place', 'visit', 'visit_item', 'media', 'media_variant', 'category', 'cuisine', 'food_type', 'visit_item_allergen', 'visit_item_media', 'visit_tag'])->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all();
    }
}
