<div class="item-editor stack" data-evidence>
    <x-ui.combobox name="items[{{ $index }}][allergens][{{ $eIndex }}][id]" label="Alergen" kind="allergen" :create="false" :selected="$evidence['option'] ?? $evidence['id'] ?? null" />
    <x-ui.select name="items[{{ $index }}][allergens][{{ $eIndex }}][exposure_status]" label="Status informasi" :value="$evidence['exposure_status'] ?? null" :options="['contains' => 'Tercatat mengandung', 'may_contain' => 'Berpotensi mengandung']" placeholder="Pilih berdasarkan sumber" required />
    <x-ui.field name="items[{{ $index }}][allergens][{{ $eIndex }}][evidence_source]" label="Sumber informasi" :value="$evidence['evidence_source'] ?? null" required help="Misalnya label menu, keterangan penjual, atau bahan teramati. Jangan menebak dari foto." />
    <x-ui.field name="items[{{ $index }}][allergens][{{ $eIndex }}][notes]" label="Catatan bukti" :value="$evidence['notes'] ?? null" />
    <x-ui.button type="button" variant="destructive" data-remove-evidence>Hapus keterangan</x-ui.button>
</div>
