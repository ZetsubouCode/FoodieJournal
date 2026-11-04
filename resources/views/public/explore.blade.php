<x-layout.public title="Jelajah pengalaman" :canonical="url('explore')" :noindex="count(request()->query()) > 0">
    <h1>Jelajah pengalaman</h1><p class="muted">Cerita kunjungan, hidangan yang dicoba, dan tempat yang disinggahi.</p>
    <nav class="tabs" aria-label="Sudut pandang hasil">@foreach(['visit' => 'Kunjungan', 'food' => 'Hidangan', 'place' => 'Tempat'] as $key => $label)<a href="{{ $state->tabUrl($key) }}" @if($state->get('tab') === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
    @foreach($state->notices as $notice)<x-ui.alert>{{ $notice }}</x-ui.alert>@endforeach
    <form method="get" action="{{ url('explore') }}" class="toolbar">
        <x-ui.query-inputs :values="$state->values" :except="['q', 'sort', 'page']" />
        <div class="search"><x-ui.field name="q" label="Cari pengalaman" :value="$state->get('q')" maxlength="120" type="search" /></div>
        <label class="field">Urutkan<select name="sort">@foreach(\App\Support\ExploreState::SORTS[$state->get('tab')] as $key => $label)<option value="{{ $key }}" @selected($state->get('sort') === $key)>{{ $label }}</option>@endforeach</select></label>
        <x-ui.button type="submit">Cari & urutkan</x-ui.button><x-ui.button type="button" data-dialog-open="explore-filters">Filter{{ count($chips) ? ' ('.count($chips).')' : '' }}</x-ui.button>
    </form>
    <div class="cluster">@foreach(['city', 'food_type', 'rating_min'] as $key)@if($key === 'rating_min' || count($options[$key]))<x-ui.button type="button" variant="tertiary" data-filter-quick="{{ $key }}">{{ $key === 'rating_min' ? 'Rating' : \App\Support\PublicOptions::LABELS[$key] }}</x-ui.button>@endif @endforeach</div>
    @if($chips)<div class="cluster section" aria-label="Filter aktif">@foreach($chips as $chip)<a class="chip" href="{{ $chip['url'] }}" aria-label="Hapus filter {{ $chip['label'] }}">{{ $chip['label'] }} ×</a>@endforeach<a href="{{ url('explore').'?tab='.$state->get('tab').'&view='.$state->get('view') }}">Hapus semua filter</a></div>@endif
    @if($state->get('avoid_allergen'))<x-ui.alert severity="warning" title="Hanya menyembunyikan paparan yang tercatat">{{ config('foodie.allergen_disclaimer') }} Hasil kunjungan/tempat berarti setidaknya satu hidangan cocok, bukan seluruh hidangan. Informasi yang belum tersedia tetap dapat muncul.</x-ui.alert>@endif
    <div class="split section"><p class="numeric">{{ $results->total() }} {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$state->get('tab')] }}</p><nav class="tabs" aria-label="Tampilan hasil">@foreach(['grid' => 'Grid', 'list' => 'Daftar'] as $key => $label)<a href="{{ $state->url(['view' => $key]) }}" @if($state->get('view') === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav></div>
    @if($results->isEmpty())<x-ui.empty-state title="Belum ada pengalaman yang cocok" description="Coba kata lain atau hapus sebagian filter. Pilihanmu tetap tersimpan di URL." :href="url('explore').'?tab='.$state->get('tab')" action="Reset filter" />@else
    <div class="results {{ $state->get('view') === 'list' ? 'results--list' : '' }}">@foreach($results as $result)
        @if($state->get('tab') === 'visit')<x-domain.visit-card :visit="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@elseif($state->get('tab') === 'food')<x-domain.food-card :item="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@else<x-domain.place-card :place="$result" :allergen-warning="(bool)$state->get('avoid_allergen')" />@endif
    @endforeach</div>@endif
    <x-ui.pagination :paginator="$results" />
    <x-domain.filter-panel :state="$state" :options="$options" :total="$results->total()" />
</x-layout.public>
