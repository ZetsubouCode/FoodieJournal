<?php

namespace Tests\Feature;

use App\Models\Visit;
use App\Services\VendingMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VendingMachineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pagination_contains_real_published_visits_without_fillers_or_private_metadata(): void
    {
        $public = Visit::factory()->published()->count(13)->create(['visited_at' => '2026-11-01']);
        $private = Visit::factory()->private()->create(['title' => 'PRIVATE-TUBE-SECRET']);
        Visit::factory()->create(['title' => 'DRAFT-TUBE-SECRET']);
        $deleted = Visit::factory()->published()->create(['title' => 'DELETED-TUBE-SECRET']);
        $deleted->delete();
        $all = [];
        foreach ([1 => 6, 2 => 6, 3 => 1] as $page => $count) {
            $response = $this->withHeader('X-Vending-Fragment', '1')->get('/?machine_page='.$page)->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private')->assertDontSee('<!doctype html>', false)
                ->assertDontSee($private->title)->assertDontSee('DRAFT-TUBE-SECRET')->assertDontSee('DELETED-TUBE-SECRET');
            $vending = $response->viewData('vending');
            $this->assertCount($count, $vending['entries']);
            $this->assertSame(13, $vending['total']);
            $this->assertSame(3, $vending['pages']);
            foreach ($vending['entries'] as $entry) {
                $this->assertStringContainsString('/visit/', $entry['href']);
                $this->assertNull($entry['image']);
                $all[] = $entry['id'];
            }
        }
        $this->assertSame($public->pluck('id')->reverse()->values()->all(), $all);
        $this->assertSame(1, app(VendingMachine::class)->page(-99)['page']);
        $this->assertSame(3, app(VendingMachine::class)->page(999)['page']);
    }

    public function test_empty_and_single_visit_remain_honest_and_links_work_without_javascript(): void
    {
        $this->get('/')->assertOk()->assertSee('data-total="0"', false)->assertSee('Arsip masih kosong.');
        $visit = Visit::factory()->published()->create();
        $this->get('/')->assertOk()->assertSee('data-total="1"', false)->assertSee(route('visit.show', $visit->slug), false)
            ->assertSee('data-visit-print', false)->assertSee('data-lamp-cradle', false)->assertSee('data-lamp-socket', false);
        $this->assertCount(1, app(VendingMachine::class)->page()['entries']);
        $visit->update(['status' => 'private']);
        $this->get('/?machine_page=1', ['X-Vending-Fragment' => '1'])->assertOk()->assertDontSee($visit->display_title);
        $this->get('/visit/'.$visit->slug)->assertNotFound();
    }

    public function test_card_media_is_bound_to_its_own_visit_and_revocation_remains_enforced(): void
    {
        $visit = Visit::factory()->published()->create();
        $photo = $visit->media()->create(['uuid' => (string) Str::uuid(), 'original_path' => 'fixture', 'mime_type' => 'image/jpeg', 'original_bytes' => 10, 'width' => 900, 'height' => 600, 'sha256' => str_repeat('a', 64)]);
        $photo->variants()->create(['variant' => 'card', 'path' => 'fixture', 'mime_type' => 'image/jpeg', 'width' => 900, 'height' => 600, 'bytes' => 10, 'status' => 'ready']);
        $visit->update(['cover_media_id' => $photo->id]);
        $entry = app(VendingMachine::class)->page()['entries'][0];
        $this->assertSame(url('media/'.$photo->uuid.'/card'), $entry['image']);
        $this->assertSame($visit->id, $entry['id']);
        $visit->update(['status' => 'private']);
        $this->get('/')->assertDontSee($photo->uuid);
        $this->get('/media/'.$photo->uuid.'/card')->assertNotFound();
    }
}
