<x-layout.public title="Foodie" :image="$featured?->coverMedia?->variant('hero') ? url('media/'.$featured->coverMedia->uuid.'/hero') : null">
    @if($featured)
        <section class="hero {{ $featured->coverMedia?->variant('hero') ? '' : 'hero--text' }}" aria-label="Catatan pilihan">
            @if($featured->coverMedia?->variant('hero'))<a href="{{ url('visit/'.$featured->slug) }}" aria-label="Baca {{ $featured->display_title }}"><x-domain.image :media="$featured->coverMedia" variant="hero" :priority="true" /></a>@endif
            <div class="hero-copy"><div class="hero-meta"><span>{{ \App\Support\Presentation::localTime($featured->visited_at, 'd M Y') }}</span><span>{{ $featured->place->city_name }}</span></div>
                <h1>{{ $featured->display_title }}</h1><p class="muted">{{ $featured->place->name }}</p><p class="reading">{{ \Illuminate\Support\Str::limit($featured->experience, 180) }}</p>
                <p><x-domain.rating :value="$featured->overall_rating" /></p><x-ui.button href="{{ url('visit/'.$featured->slug) }}" variant="primary">Baca kunjungan</x-ui.button>
            </div>
        </section>
        @if($visits->isNotEmpty())<section class="section"><div class="split"><h2>Kunjungan terbaru</h2><x-ui.button href="{{ url('explore') }}">Jelajahi pengalaman</x-ui.button></div><div class="results">@foreach($visits as $visit)<x-domain.visit-card :visit="$visit" />@endforeach</div></section>@else<div class="section"><x-ui.button href="{{ url('explore') }}">Jelajahi pengalaman</x-ui.button></div>@endif
        @if($picks->isNotEmpty())<section class="section"><h2>Pilihan pribadi</h2><p class="muted">Rekomendasi editorial, terpisah dari nilai rating.</p><div class="results">@foreach($picks as $visit)<x-domain.visit-card :visit="$visit" />@endforeach</div></section>@endif
        @if(count($options['city']) || count($options['food_type']))<section class="section"><h2>Mulai dari rasa penasaranmu</h2><div class="cluster">@foreach(array_slice($options['city'], 0, 6) as $option)<a class="chip" href="{{ url('explore').'?'.http_build_query(['city' => [$option['value']]]) }}">{{ $option['label'] }}</a>@endforeach @foreach(array_slice($options['food_type'], 0, 6) as $option)<a class="chip" href="{{ url('explore').'?'.http_build_query(['tab' => 'food', 'food_type' => [$option['value']]]) }}">{{ $option['label'] }}</a>@endforeach</div></section>@endif
    @else
        <div class="hero hero--text"><div class="hero-copy"><h1>Catatan rasa, satu kunjungan pada satu waktu.</h1><p class="reading muted">Belum ada kunjungan yang dipublikasikan. Cerita, hidangan, dan tempat akan hadir di sini.</p><x-ui.button href="{{ url('explore') }}" variant="primary">Jelajahi pengalaman</x-ui.button></div></div>
    @endif
</x-layout.public>
