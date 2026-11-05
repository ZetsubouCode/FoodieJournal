<x-layout.public :title="$place->name" :description="'Riwayat kunjungan ke '.$place->name.($place->city_name ? ' di '.$place->city_name : '').'. Rating adalah opini satu penulis.'">
    <x-site.breadcrumb :items="['Jelajah' => url('explore').'?tab=place', $place->name => null]" />
    <header class="section reading"><h1>{{ $place->name }}</h1><p>{{ collect([$place->city_name, $place->province_name])->filter()->join(', ') }}</p>@if($place->address)<p class="prose">{{ $place->address }}</p>@endif
        <p><x-domain.rating :value="$place->rating_score !== null ? (float)$place->rating_score : null" label="Rata-rata kunjungan" /> · {{ $place->rated_visit_count }} kunjungan dinilai</p>
        @if($place->maps_url)<x-ui.button href="{{ $place->maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps</x-ui.button>@endif
    </header>
    <section class="section"><h2>Riwayat kunjungan</h2><p class="muted">Harga, hidangan, dan opini merupakan catatan pada tanggal masing-masing kunjungan.</p><div class="results results--visit results--list">@foreach($visits as $visit)<x-domain.visit-card :visit="$visit" />@endforeach</div><x-ui.pagination :paginator="$visits" /></section>
</x-layout.public>
