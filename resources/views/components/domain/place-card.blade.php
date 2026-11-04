@props(['place', 'allergenWarning' => false])
<article class="journal-card"><a href="{{ url('place/'.$place->slug) }}">
    <x-domain.image :media="$place->visits->first()?->coverMedia" />
    <div><div class="metadata">{{ $place->city_name ?: 'Lokasi belum dicatat' }}</div><h3>{{ $place->name }}</h3><x-domain.rating :value="$place->rating_score !== null ? (float)$place->rating_score : null" /><p class="metadata">{{ $place->rated_visit_count }} kunjungan dinilai · Terakhir {{ \App\Support\Presentation::localTime($place->latest_visit_at, 'd M Y') }}</p>
    @if($allergenWarning)<p class="muted">Kecocokan pada setidaknya satu hidangan. Informasi alergen mungkin belum tersedia.</p>@endif</div>
</a></article>
