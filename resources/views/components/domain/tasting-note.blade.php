@props(['item', 'photo' => null, 'reverse' => false])
@php
    use App\Support\Presentation;

    $longReview = $photo && mb_strlen(trim($item->notes ?? '')) > 520;
    $hasReview = trim($item->notes ?? '') !== '';
@endphp
<article @class(['tasting-note', 'tasting-note--text' => !$photo, 'tasting-note--reverse' => $reverse]) id="hidangan-{{ $item->id }}" aria-labelledby="dish-{{ $item->id }}-title">
    @if($photo)
        <x-domain.photo-link :photo="$photo" :label="'Lihat foto '.$item->menu_name" sizes="(min-width: 2560px) 1200px, (min-width: 880px) 46vw, calc(100vw - 2rem)" class="tasting-note__photo" data-item-photo="{{ $photo->uuid }}" />
    @endif

    <div class="tasting-note__paper">
        <header class="tasting-note__heading">
            <div>
                @if($item->foodType)
                    <a class="tasting-note__type" href="{{ url('explore').'?'.http_build_query(['tab' => 'food', 'food_type' => [$item->foodType->slug]]) }}">{{ $item->foodType->name }}</a>
                @endif
                <h3 id="dish-{{ $item->id }}-title">{{ $item->menu_name }}</h3>
            </div>
            <x-domain.rating :value="$item->food_rating !== null ? (float) $item->food_rating : null" label="Rating hidangan" :stars="true" />
        </header>

        <div class="tasting-note__copy">
            @if($hasReview && !$longReview)<p class="prose tasting-note__notes">{{ $item->notes }}</p>@endif
            <dl class="tasting-note__facts">
                <div>
                    <dt>Harga satuan saat dipesan</dt>
                    <dd class="numeric">{{ Presentation::price($item->unit_price_idr) }}</dd>
                </div>
                @if($item->quantity !== null && (float) $item->quantity !== 1.0)
                    <div><dt>Jumlah</dt><dd class="numeric">{{ (float) $item->quantity }}</dd></div>
                @endif
                @if($item->cuisines->isNotEmpty())
                    <div><dt>Asal kuliner</dt><dd>{{ $item->cuisines->pluck('name')->join(', ') }}</dd></div>
                @endif
                @if($item->styles->isNotEmpty())
                    <div><dt>Teknik memasak</dt><dd>{{ $item->styles->pluck('name')->join(', ') }}</dd></div>
                @endif
                @if($item->foodType?->category)
                    <div><dt>Kategori</dt><dd>{{ $item->foodType->category->name }}</dd></div>
                @endif
            </dl>

            <details>
                <summary>Bahan &amp; informasi alergen</summary>
                @if($item->ingredients->isNotEmpty())
                    <p>Bahan yang diketahui (bukan resep lengkap)</p>
                    <ul class="tasting-note__ingredients">
                        @foreach($item->ingredients as $term)
                            <li>{{ $term->name }}@if($term->pivot->knowledge_source) · Sumber: {{ $term->pivot->knowledge_source }}@endif @if($term->pivot->notes) · {{ $term->pivot->notes }}@endif</li>
                        @endforeach
                    </ul>
                @endif
                @if($item->allergens->isEmpty())
                    <p>Informasi belum tersedia untuk alergen.</p>
                @else
                    <ul>
                        @foreach($item->allergens as $term)
                            <li><strong>{{ $term->label_id }}</strong>: {{ $term->pivot->exposure_status === 'contains' ? 'Tercatat mengandung' : 'Berpotensi mengandung' }}. Sumber: {{ $term->pivot->evidence_source }}@if($term->pivot->notes) · {{ $term->pivot->notes }}@endif</li>
                        @endforeach
                    </ul>
                    <p>Informasi alergen lain mungkin belum tersedia.</p>
                @endif
                <p>{{ config('foodie.allergen_disclaimer') }}</p>
            </details>
        </div>
    </div>

    @if($longReview)
        <div class="tasting-note__spread"><p class="prose">{{ $item->notes }}</p></div>
    @endif
</article>
