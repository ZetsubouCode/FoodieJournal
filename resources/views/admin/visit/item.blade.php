<details class="item-editor" open data-item>
    <summary>{{ $row['menu_name'] ?? 'Hidangan baru' }}</summary>
    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
    <div class="stack">
        <x-ui.field name="items[{{ $index }}][menu_name]" label="Nama menu saat dipesan" :value="$row['menu_name'] ?? null" required />
        <div class="form-grid"><x-ui.combobox name="items[{{ $index }}][food_type_id]" label="Jenis hidangan" kind="food-type" :selected="$row['food_type_option'] ?? $row['food_type_id'] ?? null" /><x-ui.rating-input name="items[{{ $index }}][taste_rating]" label="Rating rasa" :value="$row['taste_rating'] ?? null" />
        <x-ui.field name="items[{{ $index }}][quantity]" label="Jumlah" type="number" step="0.01" min="0.01" :value="$row['quantity'] ?? null" /><x-ui.field name="items[{{ $index }}][unit_price_idr]" label="Harga satuan saat dipesan (Rp)" type="number" min="0" step="1" :value="$row['unit_price_idr'] ?? null" /></div>
        <div class="form-grid"><x-ui.combobox name="items[{{ $index }}][cuisines]" label="Asal/tradisi kuliner" kind="cuisine" :multiple="true" :selected="$row['cuisines'] ?? []" /><x-ui.combobox name="items[{{ $index }}][styles]" label="Teknik memasak" kind="style" :multiple="true" :selected="$row['styles'] ?? []" /></div>
        <x-ui.field name="items[{{ $index }}][notes]" label="Catatan hidangan" :value="$row['notes'] ?? null" textarea />
        <details><summary>Bahan & informasi alergen (opsional)</summary><div class="stack">
            <x-ui.combobox name="items[{{ $index }}][ingredients]" label="Bahan yang diketahui" kind="ingredient" :multiple="true" :selected="$row['ingredients'] ?? []" />
            <div data-ingredient-sources data-index="{{ $index }}" data-sources="{{ json_encode($row['ingredient_sources'] ?? []) }}"></div>
            <p class="muted">Tidak ada catatan berarti belum diketahui. {{ config('foodie.allergen_disclaimer') }}</p>
            <div data-evidence-list data-index="{{ $index }}">@foreach($row['allergens'] ?? [] as $eIndex => $evidence)@include('admin.visit.evidence', ['index' => $index, 'eIndex' => $eIndex, 'evidence' => $evidence])@endforeach</div>
            <x-ui.button type="button" data-add-evidence>Tambah keterangan alergen</x-ui.button>
            <template data-evidence-template>@include('admin.visit.evidence', ['index' => $index, 'eIndex' => '__EVIDENCE__', 'evidence' => []])</template>
        </div></details>
        @if(isset($visit) && $visit->exists && $visit->media->isNotEmpty())<fieldset><legend>Foto dari galeri ini</legend>@foreach($visit->media as $media)<label class="check"><input type="checkbox" name="items[{{ $index }}][media][]" value="{{ $media->id }}" @checked(in_array($media->id, $row['media'] ?? []))>Foto #{{ $media->id }} · {{ $media->alt_text }}</label>@endforeach</fieldset>@endif
        <x-ui.button type="button" variant="destructive" data-remove-item>Hapus hidangan</x-ui.button>
    </div>
</details>
