<x-layout.admin :title="$place->exists ? 'Edit tempat' : 'Tambah tempat'">
    <x-site.breadcrumb :items="['Tempat' => url('admin/place'), $place->exists ? $place->name : 'Tambah tempat' => null]" />
    <h1>{{ $place->exists ? 'Edit tempat' : 'Tambah tempat' }}</h1>
    <form method="post" action="{{ $place->exists ? url('admin/place/'.$place->id) : url('admin/place') }}" class="stack" data-unsaved>@csrf @if($place->exists)@method('PUT')@endif
        <div class="form-grid"><x-ui.field name="name" label="Nama tempat" :value="$place->name" required /><x-ui.field name="city_name" label="Kota" :value="$place->city_name" /><x-ui.field name="province_name" label="Provinsi" :value="$place->province_name" /><x-ui.field name="maps_url" label="Tautan Google Maps" type="url" :value="$place->maps_url" help="Tautan http atau https. Opsional." /></div>
        <x-ui.field name="address" label="Alamat" :value="$place->address" textarea />
        <div class="form-grid"><x-ui.field name="latitude" label="Lintang" type="number" step="any" :value="$place->latitude" /><x-ui.field name="longitude" label="Bujur" type="number" step="any" :value="$place->longitude" /></div>
        <details><summary>Edit slug</summary><x-ui.field name="slug" label="Slug" :value="$place->slug" help="Kosong saat membuat untuk menghasilkan otomatis. Perubahan slug lama membuat redirect." /></details>
        <label class="check"><input type="checkbox" name="confirm_duplicate" value="1" @checked(old('confirm_duplicate'))>Saya memastikan ini tempat berbeda bila nama dan kota serupa.</label>
        <div class="action-group"><x-ui.button href="{{ url('admin/place') }}">Batal</x-ui.button><x-ui.button type="submit" variant="primary">Simpan tempat</x-ui.button></div>
    </form>
    @if($place->exists)<form method="post" action="{{ url('admin/place/'.$place->id) }}" class="section" data-confirm="Hapus tempat ini? Tempat yang memiliki riwayat tidak dapat dihapus.">@csrf @method('DELETE')<x-ui.button type="submit" variant="destructive">Hapus tempat</x-ui.button></form>@endif
</x-layout.admin>
