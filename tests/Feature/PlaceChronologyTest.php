<?php

namespace Tests\Feature;

use App\Models\RecommendationLabel;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceChronologyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function periods($response): array
    {
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);

        return array_map(fn ($node) => trim($node->textContent), iterator_to_array($xpath->query('//p[contains(@class,"place-history__period")]')));
    }

    public function test_month_groups_use_local_dates_preserve_id_ties_and_each_visits_own_rating_and_price(): void
    {
        $december = Visit::factory()->published()->create(['title' => 'Catatan Desember', 'visited_at' => '2025-12-31 16:30:00', 'service_rating' => 2.5, 'total_spent_idr' => 17000]);
        $january = Visit::factory()->published()->create(['place_id' => $december->place_id, 'title' => 'Catatan Januari', 'visited_at' => '2025-12-31 18:30:00', 'service_rating' => 4.5, 'total_spent_idr' => 93000]);
        $tied = Visit::factory()->published()->create(['place_id' => $december->place_id, 'title' => 'Tanggal sama tanpa skor', 'visited_at' => $january->visited_at, 'total_spent_idr' => 0]);
        $undated = Visit::factory()->published()->create(['place_id' => $december->place_id, 'title' => 'Tanpa tanggal tercatat', 'visited_at' => null]);
        $tied->recommendations()->attach(RecommendationLabel::create(['name' => 'Pilihan tanpa skor']));
        $url = '/place/'.$december->place->slug;
        $response = $this->get($url)->assertOk()->assertSeeInOrder([$tied->title, $january->title, $december->title, $undated->title])
            ->assertSee('2,5')->assertSee('4,5')->assertSee('Belum Dinilai')->assertSee('Pilihan tanpa skor')->assertDontSee('0,0');
        $this->assertSame(['Januari 2026', 'Desember 2025', 'Tanggal belum dicatat'], $this->periods($response));
        $this->assertSame([$tied->id, $january->id, $december->id, $undated->id], $response->viewData('visits')->pluck('id')->all());
        $this->get('/visit/'.$december->slug)->assertOk()->assertSee('Rp17.000')->assertSee('2,5')->assertDontSee('Rp93.000');
        $this->get('/visit/'.$january->slug)->assertOk()->assertSee('Rp93.000')->assertSee('4,5')->assertDontSee('Rp17.000');
        $this->get('/visit/'.$tied->slug)->assertOk()->assertSee('Rp0')->assertSee('Penilaian belum dicatat');
    }

    public function test_groups_are_only_from_the_current_published_page_and_repeat_honestly_across_the_page_boundary(): void
    {
        $oldest = Visit::factory()->published()->create(['visited_at' => '2025-12-01']);
        $recent = Visit::factory()->published()->count(19)->create(['place_id' => $oldest->place_id, 'visited_at' => '2026-01-01']);
        $hidden = Visit::factory()->private()->create(['place_id' => $oldest->place_id, 'visited_at' => '2027-01-01', 'title' => 'PRIVATE-CHRONOLOGY']);
        Visit::factory()->create(['place_id' => $oldest->place_id, 'title' => 'DRAFT-CHRONOLOGY']);
        $url = '/place/'.$oldest->place->slug;
        $first = $this->get($url)->assertOk()->assertDontSee($hidden->title)->assertDontSee('DRAFT-CHRONOLOGY');
        $this->assertSame(['Januari 2026'], $this->periods($first));
        $this->assertCount(18, $first->viewData('visits'));
        $this->assertSame(20, $first->viewData('visits')->total());
        $second = $this->get($url.'?page=2')->assertOk();
        $this->assertSame(['Januari 2026', 'Desember 2025'], $this->periods($second));
        $this->assertSame([$recent->first()->id, $oldest->id], $second->viewData('visits')->pluck('id')->all());
        $empty = $this->get($url.'?page=99')->assertOk()->assertSee('Halaman ini sudah kosong')->assertSee('Ke halaman pertama');
        $this->assertSame([], $this->periods($empty));
        $recent->each->update(['status' => 'private']);
        $this->assertSame(['Desember 2025'], $this->periods($this->get($url)->assertOk()));
        $oldest->delete();
        $this->get($url)->assertNotFound();
    }
}
