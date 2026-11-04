<x-layout.admin :title="$label">
    <h1>Taksonomi</h1>
    <nav aria-label="Jenis taksonomi" class="tabs">@foreach(\App\Support\Taxonomies::TYPES as $key => $type)<a href="{{ url('admin/taxonomy/'.$key) }}" @if($key === $kind) aria-current="page" @endif>{{ $type[1] }}</a>@endforeach</nav>
    <section class="section"><h2>{{ $label }}</h2>
        <form method="get" class="toolbar"><div class="search"><x-ui.field name="q" label="Cari istilah" :value="request('q')" /></div><x-ui.button type="submit">Cari</x-ui.button></form>
        @if($kind === 'allergen')<x-ui.alert title="Daftar terkendali">Referensi FDA9 dan EU14. Gandum berbeda dari kelompok serealia mengandung gluten (gandum, rye, barley, oat). Daftar ini bukan sertifikasi. {{ config('foodie.allergen_disclaimer') }}</x-ui.alert>@endif
        @foreach($terms as $term)<div class="split item-entry"><div><strong>{{ $term->name ?? $term->label_id }}</strong> <small>{{ $term->is_active ? 'Aktif' : 'Nonaktif' }}</small>@if($kind === 'allergen')<p class="muted">{{ $term->code }} · {{ $term->label_en }}</p>@endif</div><div class="action-group">
            @if($kind === 'allergen')<form method="post" action="{{ url('admin/taxonomy/'.$kind.'/'.$term->id) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $term->is_active ? 0 : 1 }}"><x-ui.button type="submit">{{ $term->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-ui.button></form>@else
            <x-ui.button href="{{ url('admin/taxonomy/'.$kind).'?edit='.$term->id.'#form-istilah' }}">Edit</x-ui.button><form method="post" action="{{ url('admin/taxonomy/'.$kind.'/'.$term->id) }}" data-confirm="Hapus istilah ini? Istilah yang dipakai harus dinonaktifkan.">@csrf @method('DELETE')<x-ui.button type="submit" variant="destructive">Hapus</x-ui.button></form>@endif
        </div></div>@endforeach
        <x-ui.pagination :paginator="$terms" />
    </section>
    @if($kind !== 'allergen')<section id="form-istilah" class="section reading"><h2>{{ $editing->exists ? 'Edit istilah' : 'Tambah istilah' }}</h2>
        <form method="post" action="{{ url('admin/taxonomy/'.$kind.($editing->exists ? '/'.$editing->id : '')) }}" class="stack" data-unsaved>@csrf @if($editing->exists)@method('PUT')@endif
            <x-ui.field name="name" label="Nama" :value="$editing->name" required />
            @if(in_array($kind, ['category', 'cuisine']))<x-ui.combobox name="parent_id" label="Induk" :kind="$kind" :selected="$editing->parent_id" :create="false" />@endif
            @if($kind === 'food-type')<x-ui.combobox name="category_id" label="Kategori umum" kind="category" :selected="$editing->category_id" />@endif
            @if($kind === 'cuisine')<label class="field">Klasifikasi<select name="classification">@foreach(['tradition' => 'Tradisi', 'region' => 'Wilayah', 'editorial' => 'Editorial'] as $value => $text)<option value="{{ $value }}" @selected(old('classification', $editing->classification) === $value)>{{ $text }}</option>@endforeach</select></label>@endif
            <details><summary>Edit slug</summary><x-ui.field name="slug" label="Slug" :value="$editing->slug" help="Kosong pada istilah baru untuk menghasilkan otomatis." /></details>
            <input type="hidden" name="is_active" value="0"><label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing->exists ? $editing->is_active : true))>Aktif untuk pilihan baru</label>
            <x-ui.button type="submit" variant="primary">Simpan istilah</x-ui.button>
        </form>
    </section><x-domain.inline-create />@endif
</x-layout.admin>
