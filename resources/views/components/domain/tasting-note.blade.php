@props(['item'])
<article class="tasting-note {{ $item->media->first(fn ($photo) => $photo->variant('card')) ? '' : 'tasting-note--text' }}" id="hidangan-{{ $item->id }}">
    @php($photo = $item->media->first(fn ($photo) => $photo->variant('card')))
    @if($photo)<a class="tasting-note__photo" href="#galeri" aria-label="Lihat foto {{ $item->menu_name }} di galeri"><x-domain.image :media="$photo" variant="card" sizes="(min-width: 960px) 40vw, 100vw" /></a>@endif
    <div class="tasting-note__copy">
        @if($item->foodType)<a class="tasting-note__type" href="{{ url('explore').'?'.http_build_query(['tab' => 'food', 'food_type' => [$item->foodType->slug]]) }}">{{ $item->foodType->category?->name }} · {{ $item->foodType->name }}</a>@endif
        <h3>{{ $item->menu_name }}</h3>
        <div class="tasting-note__facts"><x-domain.price :value="$item->unit_price_idr" label="Harga satuan saat dipesan" />@if($item->quantity !== null && (float)$item->quantity != 1)<span class="numeric">Jumlah: {{ (float)$item->quantity }}</span>@endif @if($item->taste_rating !== null)<x-domain.rating :value="(float)$item->taste_rating" label="Rasa" />@endif</div>
        @if($item->notes)<p class="prose tasting-note__notes">{{ $item->notes }}</p>@endif
        @if($item->cuisines->isNotEmpty())<p class="tasting-note__taxonomy">Asal/tradisi kuliner: {{ $item->cuisines->pluck('name')->join(', ') }}</p>@endif
        @if($item->styles->isNotEmpty())<p class="tasting-note__taxonomy">Teknik memasak: {{ $item->styles->pluck('name')->join(', ') }}</p>@endif
            <details><summary>Bahan & informasi alergen</summary>
                @if($item->ingredients->isNotEmpty())<p>Bahan yang diketahui (bukan resep lengkap):</p><ul>@foreach($item->ingredients as $term)<li>{{ $term->name }}@if($term->pivot->knowledge_source) · Sumber: {{ $term->pivot->knowledge_source }}@endif</li>@endforeach</ul>@endif
                @if($item->allergens->isEmpty())<p>Informasi belum tersedia untuk alergen.</p>@else<ul>@foreach($item->allergens as $term)<li><strong>{{ $term->label_id }}</strong>: {{ $term->pivot->exposure_status === 'contains' ? 'Tercatat mengandung' : 'Berpotensi mengandung' }}. Sumber: {{ $term->pivot->evidence_source }}@if($term->pivot->notes) · {{ $term->pivot->notes }}@endif</li>@endforeach</ul><p class="muted">Informasi alergen lain mungkin belum tersedia.</p>@endif
                <p class="muted">{{ config('foodie.allergen_disclaimer') }}</p>
            </details>

    </div>
</article>
