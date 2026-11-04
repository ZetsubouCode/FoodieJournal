<x-ui.modal id="inline-create" title="Tambah pilihan" description="Pilihan baru disimpan tanpa meninggalkan isian utama.">
    <form data-inline-form data-async class="stack">@csrf
        <div class="alert alert--error" role="alert" data-inline-error hidden></div>
        <x-ui.field name="inline_name" label="Nama" required maxlength="160" />
        <div data-inline-place hidden><x-ui.field name="inline_city_name" label="Kota (opsional)" maxlength="120" />
        <label class="check"><input name="confirm_duplicate" type="checkbox" value="1">Saya memastikan ini tempat berbeda jika ada kandidat serupa.</label></div>
        <div data-inline-cuisine hidden><label class="field">Klasifikasi<select name="classification"><option value="tradition">Tradisi</option><option value="region">Wilayah</option><option value="editorial">Editorial</option></select></label></div>
        <div class="action-group"><x-ui.button type="button" data-dialog-close>Batal</x-ui.button><x-ui.button type="submit" variant="primary">Simpan & pilih</x-ui.button></div>
    </form>
</x-ui.modal>
