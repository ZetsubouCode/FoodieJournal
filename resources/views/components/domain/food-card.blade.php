@props(['item', 'allergenWarning' => false])
<article class="journal-card"><a href="{{ url('visit/'.$item->visit->slug).'#hidangan-'.$item->id }}">
    <x-domain.image :media="$item->media->first(fn ($m) => $m->variant('card')) ?? $item->visit->coverMedia" />
    <div><div class="metadata">{{ $item->visit->place->name }} · {{ \App\Support\Presentation::localTime($item->visit->visited_at, 'd M Y') }}</div><h3>{{ $item->menu_name }}</h3><p class="metadata">{{ $item->cuisines->pluck('name')->join(' · ') }}{{ $item->cuisines->isNotEmpty() && $item->styles->isNotEmpty() ? ' / ' : '' }}{{ $item->styles->pluck('name')->join(' · ') }}</p>
    <div class="card-facts"><x-domain.rating :value="$item->taste_rating !== null ? (float)$item->taste_rating : null" label="Rasa" /><x-domain.price :value="$item->unit_price_idr" label="Harga satuan saat dipesan" /></div>
    @if($allergenWarning || $item->allergens->isEmpty())<p class="muted">Informasi belum tersedia{{ $allergenWarning ? ' untuk alergen yang dipilih' : ' untuk alergen' }}.</p>@else<p class="muted">Ada informasi alergen tercatat. Periksa rincian dan sumbernya.</p>@endif</div>
</a></article>
