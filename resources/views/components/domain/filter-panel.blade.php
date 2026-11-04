@props(['state', 'options', 'total'])
<x-ui.modal id="explore-filters" title="Filter pengalaman" description="Pilihan satu kelompok berarti atau; antarkelompok berarti dan, pada hidangan yang sama.">
    <form method="get" action="{{ url('explore') }}" data-filter-form data-count-url="{{ url('explore/count') }}">
        <x-ui.query-inputs :values="$state->values" :except="array_merge(\App\Support\ExploreState::DIMENSIONS, ['price_min', 'price_max', 'rating_min', 'page'])" />
        <div class="filter-groups">@foreach(\App\Support\PublicOptions::LABELS as $key => $label)@if(count($options[$key]))
            <details data-filter-dimension="{{ $key }}"><summary>{{ $label }}</summary>
                @if($key === 'avoid_allergen')<p class="muted">{{ config('foodie.allergen_disclaimer') }} Tidak ada data bukan berarti bebas alergen.</p>@endif
                @if(count($options[$key]) > 6)<label class="field">Cari pilihan {{ mb_strtolower($label) }}<input type="search" data-filter-search></label>@endif
                <fieldset><legend class="sr-only">{{ $label }}</legend>@foreach($options[$key] as $option)<label class="check" data-filter-option><input type="checkbox" name="{{ $key }}[]" value="{{ $option['value'] }}" @checked(in_array($option['value'], $state->get($key, [])))>{{ $option['label'] }}</label>@endforeach</fieldset>
            </details>
        @endif @endforeach
        <details data-filter-dimension="rating_min"><summary>Harga & rating</summary><div class="stack">
            @if($state->get('tab') !== 'place')<p>{{ $state->get('tab') === 'food' ? 'Harga satuan hidangan saat dipesan' : 'Total pengeluaran kunjungan' }} (Rp)</p><div class="form-grid"><x-ui.field name="price_min" label="Minimum" type="number" min="0" step="1" :value="$state->get('price_min')" /><x-ui.field name="price_max" label="Maksimum" type="number" min="0" step="1" :value="$state->get('price_max')" /></div>@endif
            <x-ui.field name="rating_min" label="Rating minimum" type="number" min="1" max="5" step="0.1" :value="$state->get('rating_min')" />
        </div></details></div>
        <div class="filter-actions"><p role="status" data-filter-status></p><div class="action-group"><x-ui.button type="button" data-filter-reset>Reset filter</x-ui.button><x-ui.button type="submit" variant="primary" data-filter-apply>Tampilkan {{ $total }} hasil</x-ui.button></div></div>
    </form>
</x-ui.modal>
