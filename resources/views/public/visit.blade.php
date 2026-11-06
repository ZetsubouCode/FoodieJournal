<x-layout.public :title="$visit->display_title" :description="\Illuminate\Support\Str::limit($visit->experience ?: $visit->display_title, 160)" :noindex="$preview" :admin="$preview" :image="!$preview && $visit->coverMedia?->variant('hero') ? url('media/'.$visit->coverMedia->uuid.'/hero') : null">
    @if($preview)<x-ui.alert title="Pratinjau admin">Status: {{ $visit->status }}. Ini pratinjau tersimpan; halaman ini tidak dipublikasikan otomatis. <a href="{{ url('admin/visit/'.$visit->id.'/edit') }}">Kembali ke editor</a></x-ui.alert>@endif
    <x-site.breadcrumb :items="['Jelajah' => url('explore'), $visit->display_title => null]" />
    <article class="visit-dossier">
        <header class="dossier-hero {{ $visit->coverMedia?->variant('hero') ? '' : 'dossier-hero--text' }}">
            <div class="dossier-hero__photo"><x-domain.image :media="$visit->coverMedia" variant="hero" :priority="true" sizes="(min-width: 1280px) 1280px, 100vw" /></div>
            <div class="dossier-hero__copy">
                <p class="editorial-caption">The Culinary Dossier</p>
                <h1>{{ $visit->display_title }}</h1>
                <p class="dossier-hero__place"><a href="{{ url('place/'.$visit->place->slug) }}">{{ $visit->place->name }}</a>@if($visit->place->city_name)<span>{{ $visit->place->city_name }}</span>@endif</p>
                <time datetime="{{ $visit->visited_at?->toIso8601String() }}">{{ \App\Support\Presentation::localTime($visit->visited_at, 'd M Y') }}</time>
            </div>
        </header>
        <div class="dossier-facts">
            <div><span>Tempat</span><a href="{{ url('place/'.$visit->place->slug) }}">{{ $visit->place->name }}</a></div>
            <div><span>Total pengeluaran kunjungan</span><strong class="numeric">{{ \App\Support\Presentation::price($visit->total_spent_idr) }}</strong></div>
            <div><span>Penilaian keseluruhan</span>@if($visit->overall_rating !== null)<strong class="numeric">{{ \App\Support\Presentation::rating($visit->overall_rating) }} <small>/ 5</small></strong>@else<p>Penilaian belum dicatat</p>@endif</div>
            @if($visit->place->maps_url)<a class="dossier-map" href="{{ $visit->place->maps_url }}" target="_blank" rel="noopener noreferrer">Buka Google Maps <span aria-hidden="true">↗</span></a>@endif
        </div>
        @if($visit->experience)
        <section class="dossier-story dossier-section" aria-labelledby="story-title">
            <div><h2 id="story-title">Cerita<br> <em>kunjungan.</em></h2>@if($visit->recommendations->isNotEmpty())<div class="dossier-recommendations"><p>Rekomendasi pribadi</p>@foreach($visit->recommendations as $term)<a href="{{ url('explore').'?'.http_build_query(['recommendation' => [$term->slug]]) }}">{{ $term->name }} ↗</a>@endforeach</div>@endif</div>
            <div class="dossier-story__prose prose">{{ $visit->experience }}</div>
        </section>
        @elseif($visit->recommendations->isNotEmpty())
            <div class="cluster section">@foreach($visit->recommendations as $term)<a class="chip" href="{{ url('explore').'?'.http_build_query(['recommendation' => [$term->slug]]) }}">{{ $term->name }}</a>@endforeach</div>
        @endif
        @if($visit->items->isNotEmpty())
        <section class="dossier-section dossier-tasting" aria-labelledby="tasting-title">
            <header class="editorial-heading"><h2 id="tasting-title">Hidangan yang dicoba.</h2><p>Harga dan catatan saat kunjungan.</p></header>
            @foreach($visit->items as $item)<x-domain.tasting-note :item="$item" />@endforeach
        </section>
        @endif
        <section class="dossier-section dossier-assessment" aria-labelledby="assessment-title">
            <div><h2 id="assessment-title">Catatan<br> <em>penilaian.</em></h2><p>Opini satu penulis. Nilai keseluruhan adalah rata-rata kriteria yang terisi.</p><p class="muted">Total pengeluaran dicatat tersendiri dan dapat berbeda dari jumlah harga hidangan.</p></div>
            <div class="dossier-criteria">
                @if($visit->taste_rating !== null)<div class="dossier-criterion"><h3>Rasa</h3><strong class="numeric">{{ \App\Support\Presentation::rating($visit->taste_rating) }} <small>/ 5</small></strong><p>Rata-rata hidangan yang dinilai.</p></div>@endif
                @foreach(['service' => 'Pelayanan', 'atmosphere' => 'Suasana', 'cleanliness' => 'Kebersihan', 'value' => 'Sepadan dengan biaya'] as $key => $label)
                    @if($visit->{$key.'_review'} || $visit->{$key.'_rating'} !== null)
                    <div class="dossier-criterion"><h3>{{ $label }}</h3>@if($visit->{$key.'_rating'} !== null)<strong class="numeric">{{ \App\Support\Presentation::rating((float)$visit->{$key.'_rating'}) }} <small>/ 5</small></strong>@endif @if($visit->{$key.'_review'})<p class="prose">{{ $visit->{$key.'_review'} }}</p>@endif</div>
                    @endif
                @endforeach
                @if($visit->overall_rating === null)<p>Belum ada kriteria yang dinilai.</p>@endif
            </div>
        </section>
        @if($visit->media->isNotEmpty())<section class="dossier-section dossier-gallery" id="galeri" aria-labelledby="gallery-title"><header class="editorial-heading"><h2 id="gallery-title">Di dalam arsip.</h2><p>Galeri kunjungan</p></header><x-domain.gallery :media="$visit->media" /></section>@endif
        @if($visit->conclusion)<section class="dossier-section dossier-conclusion" aria-labelledby="conclusion-title"><h2 id="conclusion-title">Yang tinggal<br> <em>setelahnya.</em></h2><p class="prose">{{ $visit->conclusion }}</p></section>@endif
        @if($visit->tags->isNotEmpty())<nav class="cluster" aria-label="Tag kunjungan">@foreach($visit->tags as $term)<a class="chip" href="{{ url('explore').'?'.http_build_query(['tag' => [$term->slug]]) }}">{{ $term->name }}</a>@endforeach</nav>@endif
        <div class="action-group section">@unless($preview)<x-ui.button href="{{ url('place/'.$visit->place->slug) }}">Lihat tempat</x-ui.button>@endunless</div>
    </article>
    @if($related->isNotEmpty())<section class="dossier-section"><header class="editorial-heading"><h2>Kembali ke tempat yang sama.</h2></header><div class="results results--visit">@foreach($related as $other)<x-domain.visit-card :visit="$other" />@endforeach</div></section>@endif
</x-layout.public>
