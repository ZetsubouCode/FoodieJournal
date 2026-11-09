@props(['visit', 'allergenWarning' => false, 'photo' => null])
@php
    $cardMedia = $photo ?? $visit->coverMedia;
    $cardTags = \App\Support\VisitCardTags::for($visit);
    $visibleTags = array_slice($cardTags, 0, 2);
    $hiddenTags = array_slice($cardTags, 2);
    $cardId = 'visit-card-'.$visit->id.'-'.\Illuminate\Support\Str::random(8);
@endphp
<article class="visit-card" aria-labelledby="{{ $cardId }}-title" data-visit-card>
    <div class="visit-card__media">
        <x-domain.image :media="$cardMedia" sizes="(min-width: 1024px) 28vw, (min-width: 700px) 35vw, 100vw" data-visit-card-image />
        @if($cardMedia?->variant('card'))<div class="photo-fallback" hidden data-visit-card-image-fallback>Foto belum dapat dimuat</div>@endif
    </div>
    <a class="visit-card__link" href="{{ route('visit.show', $visit->slug) }}" aria-label="Baca kunjungan: {{ $visit->display_title }} di {{ $visit->place->name }}"></a>
    <div class="visit-card__body">
        <p class="visit-card__eyebrow">Catatan kunjungan</p>
        <h3 id="{{ $cardId }}-title" class="visit-card__title">{{ $visit->display_title }}</h3>
        <p class="visit-card__place">{{ $visit->place->name }}</p>
        <div class="visit-card__facts">
            <span class="visit-card__rating">
                <span class="sr-only">Rating kunjungan: {{ \App\Support\Presentation::rating($visit->overall_rating) }}{{ $visit->overall_rating !== null ? ' dari 5' : '' }}</span>
                <span aria-hidden="true">@if($visit->overall_rating !== null)<b>★</b> {{ \App\Support\Presentation::rating($visit->overall_rating) }} <small>/ 5</small>@else{{ \App\Support\Presentation::rating(null) }}@endif</span>
            </span>
            @foreach($visit->recommendations as $recommendation)
                <a class="visit-card__recommendation" href="{{ url('explore').'?'.http_build_query(['recommendation' => [$recommendation->slug]], '', '&', PHP_QUERY_RFC3986) }}" aria-label="Jelajahi rekomendasi {{ $recommendation->name }}">{{ $recommendation->name }}</a>
            @endforeach
        </div>
        <x-domain.card-meta class="visit-card__meta" :date="$visit->visited_at" :location="$visit->place->city_name" />
        @if($allergenWarning)<p class="visit-card__warning">Informasi belum tersedia untuk alergen yang dipilih pada setidaknya satu hidangan.</p>@endif
        @if($cardTags)
            <div class="visit-card__tags" aria-label="Tag kunjungan">
                @foreach($visibleTags as $index => $tag)
                    <a class="visit-card__tag {{ $index === 1 ? 'visit-card__tag--second' : '' }}" href="{{ $tag['url'] }}" aria-label="Jelajahi {{ $tag['name'] }}">{{ $tag['name'] }}</a>
                @endforeach
                @if($hiddenTags)
                    <div class="visit-card__overflow" data-card-overflow>
                        <button class="visit-card__more" type="button" aria-expanded="false" aria-controls="{{ $cardId }}-tags" aria-label="Tampilkan {{ count($hiddenTags) }} tag lainnya" disabled data-js-control data-card-overflow-button>+{{ count($hiddenTags) }}</button>
                        <div class="visit-card__popover" id="{{ $cardId }}-tags" hidden data-card-overflow-panel>
                            <p>Tag lainnya</p>
                            @foreach($hiddenTags as $tag)<a href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>@endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
