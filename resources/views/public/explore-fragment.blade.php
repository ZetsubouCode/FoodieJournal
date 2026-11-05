<div class="discovery" data-discovery data-selected-index="{{ $projection['index'] }}" data-selected-label="{{ $projection['label'] }}">
    <section class="discovery-scene" aria-labelledby="discovery-title" data-discovery-scene>
        <div class="discovery-intro">
            <p class="discovery-kicker">Foodie · Jelajah</p>
            <h1 id="discovery-title">Putar rasa.<br><em>Temukan cerita.</em></h1>
            <p>Pilih jenis hidangan dari roda. Layar dan catatan di bawahnya mengikuti pilihanmu.</p>
            <p class="discovery-instruction">Tekan tombol di tengah untuk melangkah ke jenis berikutnya.</p>
        </div>
        <div class="projector" aria-label="Roda jenis hidangan">
            <img class="projector-chassis" src="{{ asset('assets/discovery/projector-chassis.webp') }}" width="1536" height="1024" alt="" aria-hidden="true">
            <div class="projector-wheel" data-wheel data-position="{{ $projection['index'] }}">
                <div class="projector-wheel__rotor" data-wheel-rotor>
                    @foreach($wheel as $index => $slot)
                        @if($slot['kind'] === 'type' || $slot['kind'] === 'all')
                            <a class="projector-sector" href="{{ $state->url(['food_type' => $slot['slug'] ? [$slot['slug']] : []], ['page', ...($slot['slug'] ? [] : ['food_type'])]) }}" data-wheel-slot="{{ $index }}" data-kind="{{ $slot['kind'] }}" aria-label="{{ $slot['label'] }}{{ $slot['count'] !== null ? ', '.$slot['count'].' hidangan diterbitkan' : '' }}" @if($projection['index'] === $index) aria-current="true" @endif>
                                @if($slot['image'])<img src="{{ $slot['image'] }}" alt="" loading="lazy" width="150" height="150">@endif
                                <span>{{ $slot['label'] }}</span>
                            </a>
                        @elseif($slot['kind'] === 'more')
                            <button class="projector-sector" type="button" data-wheel-slot="{{ $index }}" data-kind="more" disabled data-js-control aria-label="Pilih jenis hidangan lainnya"><span>{{ $slot['label'] }}</span></button>
                        @else
                            <span class="projector-sector projector-sector--empty" data-wheel-slot="{{ $index }}" data-kind="empty" aria-hidden="true"></span>
                        @endif
                    @endforeach
                    <img class="projector-wheel__metal" src="{{ asset('assets/discovery/vector/wheel-frame.svg') }}" width="900" height="900" alt="" aria-hidden="true">
                </div>
                <span class="projector-wheel__selection" aria-hidden="true"></span>
                <span class="projector-wheel__active-label" aria-hidden="true">{{ $projection['label'] }}</span>
                <button class="projector-hub" type="button" aria-label="Kategori berikutnya" aria-describedby="wheel-help" data-wheel-next disabled data-js-control @if(collect($wheel)->where('kind', 'type')->isEmpty()) data-preserve-disabled @endif><span aria-hidden="true">▶</span></button>
            </div>
            <span class="projector-lens" aria-hidden="true"></span>
        </div>
        <div class="projector-beam" aria-hidden="true"></div>
        <div class="projection" data-projection>
            <div class="projection__film" aria-hidden="true"></div>
            <div class="projection__image">
                @if($projection['image'])<img src="{{ $projection['image'] }}" srcset="{{ $projection['srcset'] }}" sizes="(min-width: 1100px) 30vw, (min-width: 700px) 45vw, 100vw" alt="{{ $projection['alt'] }}" width="{{ $projection['width'] }}" height="{{ $projection['height'] }}" fetchpriority="high">@else<div class="projection__blank">Foto belum tersedia</div>@endif
            </div>
            <div class="projection__content">
                <p>Proyeksi pilihan</p>
                <h2>{{ $projection['label'] }}</h2>
                <p class="projection__caption">{{ $projection['caption'] ?: 'Belum ada catatan yang cocok.' }}</p>
                <p class="projection__count numeric">{{ $projection['count'] }} {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$state->get('tab')] }} pada hasil ini</p>
            </div>
        </div>
    </section>
    <p id="wheel-help" class="sr-only">Tombol tengah memajukan roda satu posisi. Tautan pada roda memilih jenis secara langsung.</p>
    <p class="discovery-status sr-only" role="status" aria-live="polite" data-discovery-status></p>
    <div class="discovery-error alert alert--error" role="alert" data-discovery-error hidden><span>Jelajah belum dapat diperbarui.</span> <button type="button" class="btn" data-discovery-retry>Coba lagi</button></div>

    <nav class="tabs discovery-tabs" aria-label="Sudut pandang hasil">@foreach(['visit' => 'Kunjungan', 'food' => 'Hidangan', 'place' => 'Tempat'] as $key => $label)<a href="{{ $state->tabUrl($key) }}" @if($state->get('tab') === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
    @foreach($state->notices as $notice)<x-ui.alert>{{ $notice }}</x-ui.alert>@endforeach
    <form method="get" action="{{ url('explore') }}" class="discovery-toolbar" data-discovery-search>
        <x-ui.query-inputs :values="$state->values" :except="['q', 'sort', 'page']" />
        <label class="sr-only" for="discovery-search">Cari pengalaman</label>
        <input id="discovery-search" name="q" type="search" value="{{ $state->get('q') }}" maxlength="120" placeholder="Cari hidangan, tempat, atau cerita…">
        <x-ui.button type="submit">Cari</x-ui.button>
        <x-ui.button type="button" data-dialog-open="explore-filters">Filter{{ count($chips) ? ' ('.count($chips).')' : '' }}</x-ui.button>
        <label class="sr-only" for="discovery-sort">Urutkan</label>
        <select id="discovery-sort" name="sort" aria-label="Urutkan hasil">@foreach(\App\Support\ExploreState::SORTS[$state->get('tab')] as $key => $label)<option value="{{ $key }}" @selected($state->get('sort') === $key)>{{ $label }}</option>@endforeach</select>
        <nav class="discovery-view" aria-label="Tampilan hasil">@foreach(['grid' => 'Grid', 'list' => 'Daftar'] as $key => $label)<a href="{{ $state->url(['view' => $key]) }}" @if($state->get('view') === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
    </form>
    <div class="cluster discovery-quick">@foreach(['city', 'food_type', 'rating_min'] as $key)@if($key === 'rating_min' || count($options[$key]))<x-ui.button type="button" variant="tertiary" data-filter-quick="{{ $key }}">{{ $key === 'rating_min' ? 'Rating' : \App\Support\PublicOptions::LABELS[$key] }}</x-ui.button>@endif @endforeach</div>
    <div data-discovery-results aria-busy="false">
        @if($chips)<div class="cluster discovery-chips" aria-label="Filter aktif">@foreach($chips as $chip)<a class="chip" href="{{ $chip['url'] }}" aria-label="Hapus filter {{ $chip['label'] }}">{{ $chip['label'] }} ×</a>@endforeach<a href="{{ url('explore').'?tab='.$state->get('tab').'&view='.$state->get('view') }}">Hapus semua filter</a></div>@endif
        @if($state->get('avoid_allergen'))<x-ui.alert severity="warning" title="Hanya menyembunyikan paparan yang tercatat">{{ config('foodie.allergen_disclaimer') }} Hasil kunjungan/tempat berarti setidaknya satu hidangan cocok, bukan seluruh hidangan. Informasi yang belum tersedia tetap dapat muncul.</x-ui.alert>@endif
        <section class="memory-reel" aria-labelledby="memory-reel-title">
            <header class="memory-reel__head"><div><p class="memory-reel__index">Arsip pengalaman · {{ str_pad((string) $results->currentPage(), 3, '0', STR_PAD_LEFT) }}</p><h2 id="memory-reel-title">Memory Reel</h2></div><p class="numeric">{{ $results->total() }} {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$state->get('tab')] }}</p></header>
            @if($results->isEmpty())<x-ui.empty-state title="Belum ada pengalaman yang cocok" description="Coba kata lain atau hapus sebagian filter. Pilihanmu tetap tersimpan di URL." :href="url('explore').'?tab='.$state->get('tab')" action="Reset filter" />@else
            <div class="results {{ $state->get('tab') === 'visit' ? 'results--visit' : '' }} {{ $state->get('view') === 'list' ? 'results--list' : '' }}">@foreach($results as $result)
                @if($state->get('tab') === 'visit')<x-domain.visit-card :visit="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@elseif($state->get('tab') === 'food')<x-domain.food-card :item="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@else<x-domain.place-card :place="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@endif
            @endforeach</div>@endif
            <x-ui.pagination :paginator="$results" />
        </section>
    </div>
</div>
