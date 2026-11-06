<div class="discovery" data-discovery data-mode="{{ $state->get('tab') }}">
    <div class="discovery-error alert alert--error" role="alert" data-discovery-error hidden><span>Jelajah belum dapat diperbarui. Pilihan sebelumnya tetap ditampilkan.</span> <button type="button" class="btn" data-discovery-retry>Coba lagi</button></div>
    <div data-discovery-console id="discovery-console">
    <header class="discovery-console__head"><h2>Telusuri catatan</h2></header>
    <nav class="tabs discovery-tabs" aria-label="Sudut pandang hasil">@foreach(['visit' => 'Kunjungan', 'food' => 'Hidangan', 'place' => 'Tempat'] as $key => $label)<a href="{{ $state->tabUrl($key) }}" @if($state->get('tab') === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
    @foreach($state->notices as $notice)<x-ui.alert>{{ $notice }}</x-ui.alert>@endforeach
    <form method="get" action="{{ url('explore') }}" class="discovery-toolbar" data-discovery-search>
        <x-ui.query-inputs :values="$state->values" :except="['q', 'sort', 'page']" />
        <label class="sr-only" for="discovery-search">Cari pengalaman</label>
        <div class="discovery-search-field"><input id="discovery-search" name="q" type="search" value="{{ $state->get('q') }}" maxlength="120" placeholder="Cari hidangan, tempat, atau cerita…"><button type="submit" aria-label="Cari"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button></div>
        <x-ui.button type="button" data-dialog-open="explore-filters"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18M8 3v6m8 0v6M9 15v6"/></svg>Filter{{ count($chips) ? ' ('.count($chips).')' : '' }}</x-ui.button>
        <label class="sr-only" for="discovery-sort">Urutkan</label>
        <select id="discovery-sort" name="sort" aria-label="Urutkan hasil">@foreach(\App\Support\ExploreState::SORTS[$state->get('tab')] as $key => $label)<option value="{{ $key }}" @selected($state->get('sort') === $key)>{{ $label }}</option>@endforeach</select>
        <nav class="discovery-view" aria-label="Tampilan hasil">@foreach(['grid' => 'Grid', 'list' => 'Daftar'] as $key => $label)<a href="{{ $state->url(['view' => $key]) }}" aria-label="{{ $label }}" @if($state->get('view') === $key) aria-current="page" @endif><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">@if($key === 'grid')<path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>@else<path d="M8 5h13M8 12h13M8 19h13M3 5h1M3 12h1M3 19h1"/>@endif</svg></a>@endforeach</nav>
    </form>
    <div class="discovery-console__selection">
        @if($chips)<div class="cluster discovery-chips" aria-label="Filter aktif">@foreach($chips as $chip)<a class="chip" href="{{ $chip['url'] }}" aria-label="Hapus filter {{ $chip['label'] }}">{{ $chip['label'] }} ×</a>@endforeach<a href="{{ url('explore').'?tab='.$state->get('tab').'&view='.$state->get('view') }}">Hapus semua filter</a></div>@endif
    </div>
    </div>
    <p class="sr-only" role="status" data-browse-status></p>
    <div data-discovery-results aria-busy="false">
        @if($state->get('avoid_allergen'))<x-ui.alert severity="warning" title="Hanya menyembunyikan paparan yang tercatat">{{ config('foodie.allergen_disclaimer') }} Hasil kunjungan/tempat berarti setidaknya satu hidangan cocok, bukan seluruh hidangan. Informasi yang belum tersedia tetap dapat muncul.</x-ui.alert>@endif
        <section class="memory-reel" aria-labelledby="memory-reel-title">
            <header class="memory-reel__head"><div><p class="memory-reel__index">Arsip pengalaman · {{ str_pad((string) $results->currentPage(), 3, '0', STR_PAD_LEFT) }}</p><h2 id="memory-reel-title">Memory Reel</h2></div><p class="numeric">{{ $results->total() }} {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$state->get('tab')] }}</p></header>
            @if($results->isEmpty())<x-ui.empty-state title="Belum ada pengalaman yang cocok" description="Coba kata lain atau hapus sebagian filter. Pilihanmu tetap tersimpan di URL." :href="url('explore').'?tab='.$state->get('tab')" action="Reset filter" />@else
            <div class="results {{ $state->get('tab') === 'visit' ? 'results--visit' : '' }} {{ $state->get('view') === 'list' ? 'results--list' : '' }}">@foreach($results as $result)
                @if($state->get('tab') === 'visit')<x-domain.visit-card :visit="$result" :photo="$result->coverMedia?->variant('card') ? $result->coverMedia : $result->media->first(fn ($photo) => $photo->variant('card'))" :allergen-warning="(bool)$state->get('avoid_allergen')" />@elseif($state->get('tab') === 'food')<x-domain.food-card :item="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@else<x-domain.place-card :place="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@endif
            @endforeach</div>@endif
            <x-ui.pagination :paginator="$results" />
        </section>
    </div>
    <x-domain.filter-panel :state="$state" :options="$options" :total="$results->total()" />
</div>
