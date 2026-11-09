<x-layout.public :title="$place->name" :description="'Riwayat kunjungan ke '.$place->name.($place->city_name ? ' di '.$place->city_name : '').'. Rating adalah opini satu penulis.'">
    <x-site.breadcrumb :items="['Jelajah' => url('explore').'?tab=place', $place->name => null]" />
    <header class="place-dossier {{ $coverMedia ? '' : 'place-dossier--text' }}"><div class="place-dossier__identity"><p class="editorial-caption">Catatan tempat</p><h1>{{ $place->name }}</h1>@if($place->city_name || $place->province_name)<p>{{ collect([$place->city_name, $place->province_name])->filter()->join(', ') }}</p>@endif
        <div class="place-dossier__facts">@if($place->address)<p class="prose">{{ $place->address }}</p>@endif
        <p><x-domain.rating :value="$place->rating_score !== null ? (float)$place->rating_score : null" label="Rata-rata kunjungan" /> · {{ $place->rated_visit_count }} kunjungan dinilai</p>
        @if($place->maps_url)<x-ui.button href="{{ $place->maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</x-ui.button>@endif
        </div>
    </div>
    @if($coverMedia)<div class="place-dossier__photo"><x-domain.image :media="$coverMedia" variant="hero" :priority="true" sizes="(min-width: 1920px) 800px, (min-width: 768px) 46vw, calc(100vw - 2rem)" /></div>@endif</header>
    <section class="section place-history" aria-labelledby="history-title">
        <h2 id="history-title">Riwayat kunjungan</h2>
        <p class="muted">Harga, hidangan, dan opini merupakan catatan pada tanggal masing-masing kunjungan.</p>
        @if($visits->isEmpty())
            <x-ui.empty-state title="Halaman ini sudah kosong" description="Riwayat kunjungan masih tersedia di halaman pertama." :href="url('place/'.$place->slug)" action="Ke halaman pertama" />
        @else
            @foreach($visits->groupBy(fn ($visit) => \App\Support\Presentation::localTime($visit->visited_at, 'F Y')) as $period => $entries)
                <div class="place-history__group">
                    <p class="place-history__period numeric" id="history-period-{{ $loop->index }}">{{ $period }}</p>
                    <ol class="place-history__entries results results--visit results--list" role="list" aria-labelledby="history-period-{{ $loop->index }}">
                        @foreach($entries as $visit)<li><x-domain.visit-card :visit="$visit" /></li>@endforeach
                    </ol>
                </div>
            @endforeach
        @endif
        <x-ui.pagination :paginator="$visits" />
    </section>
</x-layout.public>
