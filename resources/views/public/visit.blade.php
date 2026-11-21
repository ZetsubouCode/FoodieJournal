@php
    use App\Support\Presentation;

    $cover = $visit->coverMedia;
    if ($cover?->visit_id === $visit->id && $cover->variant('hero')) {
        $disk = \Illuminate\Support\Facades\Storage::disk('media');
        $cover = clone $cover;
        $cover->setRelation('variants', $cover->variants->filter(fn ($variant) => $variant->status === 'ready' && $disk->exists($variant->path)));
    }
    $cover = $cover?->visit_id === $visit->id && $cover->variant('hero') ? $cover : null;

    $galleryMedia = $visit->media->filter(fn ($photo) => $photo->variant('card') || $photo->variant('thumbnail'))->keyBy('id');
    $observations = array_filter([
        'positive' => Presentation::observationLines($visit->pros),
        'negative' => Presentation::observationLines($visit->cons),
    ]);
    $conclusion = trim($visit->conclusion ?? '') !== '';
    $chapters = array_filter([
        'story' => trim($visit->experience ?? '') !== '' ? 'Cerita' : null,
        'tasting' => $visit->items->isNotEmpty() ? 'Daftar rasa' : null,
        'fieldnotes' => $observations ? 'Catatan' : null,
        'galeri' => $galleryMedia->isNotEmpty() ? 'Foto' : null,
        'opinion' => $conclusion || $visit->recommendations->isNotEmpty() ? 'Pendapat' : null,
    ]);
    $numbers = array_flip(array_keys($chapters));
    $eyebrow = fn ($id) => sprintf('%02d / %s', $numbers[$id] + 1, strtoupper($chapters[$id]));
    $placeUrl = url('place/'.$visit->place->slug);
@endphp
<x-layout.public :title="$visit->display_title" :description="\Illuminate\Support\Str::limit($visit->experience ?: $visit->display_title, 160)" :noindex="$preview" :admin="$preview" :image="!$preview && $cover ? url('media/'.$cover->uuid.'/hero') : null"
    :visit-place-url="$preview ? null : $placeUrl" :visit-has-tasting="isset($chapters['tasting'])">
    @if($preview)
        <x-ui.alert title="Pratinjau admin">Status: {{ $visit->status }}. Ini pratinjau tersimpan; halaman ini tidak dipublikasikan otomatis. <a href="{{ url('admin/visit/'.$visit->id.'/edit') }}">Kembali ke editor</a></x-ui.alert>
    @endif

    <article class="visit-dossier" aria-labelledby="visit-title">
        @if(!$preview && $chapters)
            <nav class="dossier-rail" aria-label="Bagian kunjungan" data-chapter-rail hidden>
                @foreach($chapters as $id => $label)
                    <a href="#{{ $id }}"><span>{{ $label }}</span></a>
                @endforeach
            </nav>
        @endif

        <header @class(['dossier-hero', 'dossier-hero--text' => !$cover])>
            @if($cover)
                <div class="dossier-hero__photo">
                    <x-domain.image :media="$cover" variant="hero" :priority="true" sizes="(min-width: 2560px) 2432px, (min-width: 1920px) calc(100vw - 8rem), (min-width: 1024px) 93.334vw, (min-width: 481px) calc(100vw - 3rem), calc(100vw - 2rem)" />
                </div>
            @endif
            <div class="dossier-hero__copy">
                <h1 id="visit-title">{{ $visit->display_title }}</h1>
                <div class="record-meta dossier-hero__meta">
                    <div class="record-meta__item dossier-hero__place">
                        <x-ui.meta-icon name="location" />
                        <span><a href="{{ $placeUrl }}">{{ $visit->place->name }}</a>@if($visit->place->city_name) · {{ $visit->place->city_name }}@endif</span>
                    </div>
                    @if($visit->visited_at)
                        <time class="record-meta__item" datetime="{{ $visit->visited_at->toIso8601String() }}"><x-ui.meta-icon name="calendar" /><span>{{ Presentation::localTime($visit->visited_at, 'j F Y') }}</span></time>
                    @else
                        <span class="record-meta__item"><x-ui.meta-icon name="calendar" /><span>{{ Presentation::localTime(null) }}</span></span>
                    @endif
                </div>
            </div>
        </header>

        <dl class="dossier-facts" aria-label="Ringkasan kunjungan">
            <div>
                <dt>Rerata hidangan</dt>
                <dd><x-domain.rating :value="$visit->food_score" :stars="true" /></dd>
                @if($visit->rated_item_count)<dd class="dossier-facts__count">{{ $visit->rated_item_count }} hidangan dinilai</dd>@endif
            </div>
            <div>
                <dt>Total pengeluaran</dt>
                <dd @class(['numeric', 'dossier-facts__missing' => $visit->total_spent_idr === null])>{{ $visit->total_spent_idr === null ? 'Belum dicatat' : Presentation::price($visit->total_spent_idr) }}</dd>
            </div>
            <div>
                <dt>Hidangan</dt>
                <dd class="numeric">{{ $visit->items->count() }}</dd>
            </div>
        </dl>

        @if(isset($chapters['story']))
            <section class="dossier-section dossier-story" id="story" aria-labelledby="story-title">
                <header>
                    <p class="dossier-eyebrow">{{ $eyebrow('story') }}</p>
                    <h2 id="story-title">Cerita <em>kunjungan.</em></h2>
                </header>
                <p class="dossier-story__prose prose">{{ $visit->experience }}</p>
            </section>
        @endif

        @if(isset($chapters['tasting']))
            <section class="dossier-section dossier-tasting" id="tasting" aria-labelledby="tasting-title">
                <header>
                    <p class="dossier-eyebrow">{{ $eyebrow('tasting') }}</p>
                    <h2 id="tasting-title">Yang dicoba. <em>Yang terasa.</em></h2>
                </header>
                @php
                    $photoCount = 0;
                @endphp
                @foreach($visit->items as $item)
                    @php
                        $photo = $item->media->first(fn ($media) => $galleryMedia->has($media->id));
                        if ($photo) {
                            $photoCount++;
                        }
                    @endphp
                    <x-domain.tasting-note :item="$item" :photo="$photo ? $galleryMedia->get($photo->id) : null" :reverse="$photo && $photoCount % 2 === 0" />
                @endforeach
            </section>
        @endif

        @if(isset($chapters['fieldnotes']))
            <section class="dossier-section dossier-observations" id="fieldnotes" aria-labelledby="observations-title">
                <header>
                    <p class="dossier-eyebrow">{{ $eyebrow('fieldnotes') }}</p>
                    <h2 id="observations-title">Di luar <em>rasa.</em></h2>
                </header>
                <div class="dossier-observations__columns">
                    @foreach($observations as $tone => $lines)
                        <div class="dossier-observations__side dossier-observations__side--{{ $tone }}">
                            <h3>{{ $tone === 'positive' ? 'Hal menyenangkan' : 'Kurang menyenangkan' }}</h3>
                            <ul>
                                @foreach($lines as $line)<li>{{ $line }}</li>@endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($chapters['galeri']))
            <section class="dossier-section dossier-gallery" id="galeri" aria-labelledby="gallery-title">
                <header>
                    <p class="dossier-eyebrow">{{ $eyebrow('galeri') }}</p>
                    <h2 id="gallery-title">Di dalam <em>arsip.</em></h2>
                </header>
                <x-domain.gallery :media="$galleryMedia" />
            </section>
        @endif

        @if(isset($chapters['opinion']))
            <section @class(['dossier-section dossier-conclusion', 'dossier-conclusion--stamps-only' => !$conclusion]) id="opinion" aria-labelledby="opinion-title">
                <div>
                    <p class="dossier-eyebrow" aria-hidden="true">{{ $eyebrow('opinion') }}</p>
                    <h2 id="opinion-title" class="sr-only">Pendapat</h2>
                </div>
                <div>
                    @if($conclusion)
                        <blockquote class="dossier-conclusion__quote prose">{{ $visit->conclusion }}</blockquote>
                    @endif
                    @if($visit->recommendations->isNotEmpty())
                        <div class="dossier-conclusion__stamps" role="group" aria-label="Rekomendasi pribadi">
                            @foreach($visit->recommendations as $term)
                                <span class="dossier-stamp">{{ $term->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if($visit->tags->isNotEmpty() || $visit->place->maps_url)
            <nav class="dossier-links cluster" aria-label="Tag dan lokasi kunjungan">
                @foreach($visit->tags as $term)
                    <a class="chip" href="{{ url('explore').'?'.http_build_query(['tag' => [$term->slug]]) }}">{{ $term->name }}</a>
                @endforeach
                @if($visit->place->maps_url)
                    <a class="dossier-map" href="{{ $visit->place->maps_url }}" target="_blank" rel="noopener noreferrer"><x-ui.meta-icon name="location" /> Buka Google Maps</a>
                @endif
            </nav>
        @endif

        @if($related->isNotEmpty())
            <section class="dossier-section dossier-related" aria-labelledby="related-title">
                <header><h2 id="related-title">Kembali ke tempat <em>yang sama.</em></h2></header>
                <div class="results results--visit">
                    @foreach($related as $other)<x-domain.visit-card :visit="$other" />@endforeach
                </div>
            </section>
        @endif
    </article>
</x-layout.public>
