@props(['visit', 'allergenWarning' => false])
<article class="journal-card"><a href="{{ url('visit/'.$visit->slug) }}">
    <x-domain.image :media="$visit->coverMedia" />
    <div><div class="metadata">{{ $visit->place->city_name }} · {{ \App\Support\Presentation::localTime($visit->visited_at, 'd M Y') }}</div><h3>{{ $visit->display_title }}</h3><p class="muted">{{ $visit->place->name }}</p>
    <div class="card-facts"><x-domain.rating :value="$visit->overall_rating" /><x-domain.price :value="$visit->total_spent_idr" label="Total kunjungan" /></div>
    @if($visit->recommendations->isNotEmpty())<p class="metadata">{{ $visit->recommendations->pluck('name')->join(' · ') }}</p>@endif
    @if($allergenWarning)<p class="muted">Informasi belum tersedia untuk alergen yang dipilih pada setidaknya satu hidangan.</p>@endif</div>
</a></article>
