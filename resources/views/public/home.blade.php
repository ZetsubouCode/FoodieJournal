<x-layout.public title="Foodie" :image="$featured?->coverMedia?->variant('hero') ? url('media/'.$featured->coverMedia->uuid.'/hero') : null">
    <div class="homepage">
        @if($featured)
            <section class="home-hero {{ $featured->coverMedia?->variant('hero') ? '' : 'home-hero--text' }}" aria-label="Kunjungan pilihan">
                @if($featured->coverMedia?->variant('hero'))
                    <x-domain.image :media="$featured->coverMedia" variant="hero" :priority="true" sizes="(min-width: 1280px) 1280px, 100vw" />
                @endif
                <div class="home-hero__copy">
                    <p class="home-eyebrow">Catatan kunjungan</p>
                    <h1>{{ $featured->display_title }}</h1>
                    <p class="home-hero__place">{{ $featured->place->name }}</p>
                    <div class="home-hero__meta">
                        <span>@if($featured->overall_rating !== null)<span aria-hidden="true">★</span> @endif{{ \App\Support\Presentation::rating($featured->overall_rating) }}@if($featured->overall_rating !== null) / 5 @endif</span>
                        @if($featured->visited_at)<time datetime="{{ $featured->visited_at->setTimezone(config('foodie.timezone'))->format('Y-m-d') }}">{{ \App\Support\Presentation::localTime($featured->visited_at, 'd M Y') }}</time>@endif
                        @if($featured->place->city_name)<span>{{ $featured->place->city_name }}</span>@endif
                    </div>
                    <a class="home-hero__action" href="{{ route('visit.show', $featured->slug) }}">Baca kunjungan <span aria-hidden="true">↗</span></a>
                </div>
            </section>
        @else
            <section class="home-hero home-hero--text" aria-label="Jurnal Foodie"><div class="home-hero__copy"><p class="home-eyebrow">Jurnal kuliner personal</p><h1>Catatan rasa, satu kunjungan pada satu waktu.</h1><p>Belum ada kunjungan yang dipublikasikan. Cerita, hidangan, dan tempat akan hadir di sini.</p><a class="home-hero__action" href="{{ route('explore.index') }}">Jelajahi pengalaman <span aria-hidden="true">↗</span></a></div></section>
        @endif

        <section class="home-section" aria-labelledby="recent-title">
            <x-home.section-heading id="recent-title" eyebrow="The latest entries" title="Kunjungan terbaru" description="Cerita dari meja-meja yang baru dikunjungi." :href="route('explore.index')" action="Semua kunjungan" />
            <x-home.visit-carousel id="recent-carousel" label="Kunjungan terbaru" :visits="$visits" />
        </section>
        <section class="home-section" aria-labelledby="picks-title">
            <x-home.section-heading id="picks-title" eyebrow="Personal picks" title="Pilihan pribadi" description="Kunjungan yang diberi rekomendasi pribadi oleh penulis." />
            <x-home.visit-carousel id="picks-carousel" label="Pilihan pribadi" :visits="$picks" :interval="7200" />
        </section>
        <section class="home-section home-discovery" aria-labelledby="discovery-title">
            <div class="home-discovery__intro"><p class="home-eyebrow">Explore by curiosity</p><h2 id="discovery-title">Temukan cerita berikutnya.</h2><p>Ikuti hidangan, kota, atau cara memasak yang membuatmu penasaran.</p></div>
            <div class="home-discovery__grid">
                <x-home.discovery-tile :tile="$discovery['food']" class="discovery-tile--food" />
                <x-home.discovery-tile :tile="$discovery['city']" class="discovery-tile--city" />
                <x-home.discovery-tile :tile="$discovery['style']" class="discovery-tile--style" />
            </div>
        </section>
    </div>
</x-layout.public>
