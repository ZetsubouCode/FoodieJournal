@props(['state', 'options', 'total'])
@php
    $groups = [
        'location' => ['label' => 'Lokasi', 'fields' => ['city']],
        'food' => ['label' => 'Kategori & jenis', 'fields' => ['category', 'food_type']],
        'style' => ['label' => 'Teknik memasak', 'fields' => ['style']],
        'cuisine' => ['label' => 'Tradisi kuliner', 'fields' => ['cuisine']],
        'rating' => ['label' => 'Harga & rating', 'fields' => []],
        'tag' => ['label' => 'Tag', 'fields' => ['tag']],
        'recommendation' => ['label' => 'Rekomendasi', 'fields' => ['recommendation']],
        'ingredient' => ['label' => 'Bahan', 'fields' => ['ingredient']],
        'allergen' => ['label' => 'Informasi alergen', 'fields' => ['avoid_allergen']],
    ];
    $groups = array_filter($groups, fn ($group, $key) => $key === 'rating' || collect($group['fields'])->contains(fn ($field) => count($options[$field])), ARRAY_FILTER_USE_BOTH);
    $firstGroup = array_key_first($groups);
@endphp
<x-ui.modal id="explore-filters" title="Filter arsip" description="Pilih kriteria, lalu terapkan bersama. Perubahan belum disimpan sampai kamu menekan Tampilkan hasil." class="archive-filter" data-filter-drawer>
    <form method="get" action="{{ url('explore') }}" data-filter-form data-count-url="{{ url('explore/count') }}">
        <x-ui.query-inputs :values="$state->values" :except="array_merge(\App\Support\ExploreState::DIMENSIONS, ['price_min', 'price_max', 'rating_min', 'page'])" />
        <div class="archive-filter__workspace">
            <nav class="archive-filter__nav" aria-label="Kelompok filter">
                @foreach($groups as $key => $group)<button type="button" data-filter-group-open="{{ $key }}" aria-controls="filter-group-{{ $key }}" aria-pressed="{{ $key === $firstGroup ? 'true' : 'false' }}"><span>{{ $group['label'] }}</span><span class="archive-filter__count numeric" data-group-count="{{ $key }}">0</span></button>@endforeach
            </nav>
            <div class="archive-filter__content">
                @foreach($groups as $key => $group)
                <section id="filter-group-{{ $key }}" data-filter-group="{{ $key }}" aria-labelledby="filter-heading-{{ $key }}" @if($key !== $firstGroup) hidden @endif>
                    <header class="archive-filter__group-head"><div><p>Kriteria pencarian</p><h3 id="filter-heading-{{ $key }}">{{ $group['label'] }}</h3></div><button type="button" class="archive-filter__reset-group" data-filter-group-reset="{{ $key }}">Hapus pilihan</button></header>
                    @if($key === 'rating')
                        <fieldset data-filter-dimension="rating_min"><legend class="sr-only">Harga & rating</legend>
                            <p class="archive-filter__help">{{ ['visit' => 'Total pengeluaran untuk satu kunjungan. Rating adalah penilaian keseluruhan kunjungan.', 'food' => 'Harga satuan hidangan saat dipesan. Rating adalah penilaian rasa hidangan.', 'place' => 'Rating adalah rata-rata kunjungan published yang telah dinilai. Filter harga tidak digunakan untuk tempat.'][$state->get('tab')] }}</p>
                            @if($state->get('tab') !== 'place')<div class="form-grid"><x-ui.field name="price_min" label="Harga minimum (Rp)" type="number" min="0" step="1" :value="$state->get('price_min')" /><x-ui.field name="price_max" label="Harga maksimum (Rp)" type="number" min="0" step="1" :value="$state->get('price_max')" /></div>@endif
                            <x-ui.field name="rating_min" :label="$state->get('tab') === 'food' ? 'Rasa minimum' : 'Rating minimum'" type="number" min="1" max="5" step="0.1" :value="$state->get('rating_min')" />
                        </fieldset>
                    @endif
                    @foreach($group['fields'] as $field)
                        <fieldset data-filter-dimension="{{ $field }}" class="archive-filter__dimension"><legend>{{ \App\Support\PublicOptions::LABELS[$field] }}</legend>
                            @if($field === 'category')<p class="archive-filter__help">Kategori induk mencakup turunannya. Kategori dan jenis dipilih terpisah.</p>@endif
                            @if($field === 'style')<p class="archive-filter__help">Pilih satu atau beberapa teknik memasak.</p>@endif
                            @if($field === 'avoid_allergen')<p class="archive-filter__help">{{ config('foodie.allergen_disclaimer') }} Tidak ada data bukan berarti bebas alergen.</p>@endif
                            @if(count($options[$field]) > 6)<label class="archive-filter__search">Cari {{ mb_strtolower(\App\Support\PublicOptions::LABELS[$field]) }}<input type="search" data-filter-search placeholder="Ketik untuk menyaring pilihan"></label>@endif
                            <div class="archive-filter__options">
                                @foreach($options[$field] as $index => $option)<label class="archive-choice archive-choice--depth-{{ min(10, $option['depth'] ?? 0) }}" data-filter-option><input id="filter-{{ $field }}-{{ $index }}" type="checkbox" name="{{ $field }}[]" value="{{ $option['value'] }}" @checked(in_array($option['value'], $state->get($field, [])))><span class="archive-choice__box" aria-hidden="true"></span><span>{{ $option['label'] }}</span></label>@endforeach
                            </div>
                            <p class="archive-filter__help" data-filter-no-options hidden>Tidak ada pilihan yang cocok.</p>
                        </fieldset>
                    @endforeach
                </section>
                @endforeach
            </div>
        </div>
        <div class="archive-filter__selection"><span class="archive-filter__pending">Pilihan belum diterapkan</span><div data-filter-selected role="group" aria-label="Pilihan filter sementara"></div></div>
        <footer class="archive-filter__actions"><p role="status" data-filter-status></p><div class="archive-filter__action-row"><x-ui.button type="button" data-filter-reset>Reset filter</x-ui.button><div class="action-group"><x-ui.button type="button" data-dialog-close>Batal</x-ui.button><x-ui.button type="submit" variant="primary" data-filter-apply>Tampilkan {{ $total }} hasil</x-ui.button></div></div></footer>
    </form>
</x-ui.modal>
