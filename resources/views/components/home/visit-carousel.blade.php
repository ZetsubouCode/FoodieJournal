@props(['id', 'label', 'visits', 'interval' => 6500])
<div class="visit-carousel" data-visit-carousel data-interval="{{ $interval }}" role="region" aria-roledescription="carousel" aria-label="{{ $label }}">
    @if($visits->isEmpty())
        <p class="home-empty">Belum ada kunjungan untuk bagian ini. <a href="{{ route('explore.index') }}">Jelajahi semua kunjungan</a>.</p>
    @else
        <div class="visit-carousel__rail">
            <div class="visit-carousel__viewport" id="{{ $id }}-viewport" data-carousel-viewport>
                <div class="visit-carousel__track" data-carousel-track>
                    @foreach($visits as $visit)
                        <div class="visit-carousel__slide" data-carousel-slide><x-domain.visit-card :visit="$visit" /></div>
                    @endforeach
                </div>
            </div>
            @if($visits->count() > 1)
                <button class="visit-carousel__arrow visit-carousel__arrow--prev" type="button" aria-label="Kunjungan sebelumnya: {{ $label }}" aria-controls="{{ $id }}-viewport" data-carousel-prev disabled data-js-control><span aria-hidden="true">←</span></button>
                <button class="visit-carousel__arrow visit-carousel__arrow--next" type="button" aria-label="Kunjungan berikutnya: {{ $label }}" aria-controls="{{ $id }}-viewport" data-carousel-next disabled data-js-control><span aria-hidden="true">→</span></button>
            @endif
        </div>
        @if($visits->count() > 1)
            <div class="visit-carousel__utility"><span data-carousel-status class="sr-only" aria-live="polite" aria-atomic="true"></span><span aria-hidden="true" data-carousel-count>1 / {{ $visits->count() }}</span><button type="button" data-carousel-toggle disabled data-js-control aria-label="Jeda putar otomatis: {{ $label }}">Jeda otomatis</button></div>
        @endif
    @endif
</div>
