<x-layout.public title="Foodie" :image="$featured?->coverMedia?->variant('hero') ? url('media/'.$featured->coverMedia->uuid.'/hero') : null">
    <div class="homepage">
        @include('public.home-archive')
        <section class="home-section" aria-labelledby="recent-title">
            <header class="editorial-heading"><div><p class="editorial-caption">Freshly archived</p><h2 id="recent-title">Kunjungan terbaru.</h2></div><a href="{{ route('explore.index') }}">Semua kunjungan <span aria-hidden="true">↗</span></a></header>
            @if($visits->isNotEmpty())
                <div class="fresh-archive">
                    <div class="fresh-archive__lead"><x-home.editorial-entry :visit="$visits->first()" :lead="true" /></div>
                    <div class="fresh-archive__notes">@foreach($visits->slice(1, 3) as $visit)<x-home.editorial-entry :visit="$visit" />@endforeach</div>
                </div>
            @else
                <p class="home-empty">Belum ada kunjungan yang dipublikasikan.</p>
            @endif
        </section>
        <section class="home-section" aria-labelledby="picks-title">
            <header class="editorial-heading"><div><h2 id="picks-title">Pilihan pribadi.</h2><p>Pengalaman yang ingin direkomendasikan penulis.</p></div></header>
            @if($picks->isNotEmpty())
                <div class="personal-gallery">@foreach($picks->take(4) as $visit)<x-home.editorial-entry :visit="$visit" :lead="$loop->first" :recommendation="true" />@endforeach</div>
            @else
                <p class="home-empty">Belum ada kunjungan dengan rekomendasi pribadi.</p>
            @endif
        </section>
    </div>
</x-layout.public>
