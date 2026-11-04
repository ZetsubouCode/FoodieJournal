<x-layout.admin :title="$quick ? 'Tambah kunjungan' : 'Edit kunjungan'">
    <x-site.breadcrumb :items="['Kunjungan' => url('admin/visit'), $quick ? 'Tambah kunjungan' : $visit->display_title => null]" />
    <div class="split"><div><h1>{{ $quick ? 'Tambah kunjungan' : $visit->display_title }}</h1><p class="muted">{{ $quick ? 'Tempat dan waktu dulu. Cerita, hidangan, dan foto bisa menyusul.' : 'Simpan setiap perubahan sebelum membuka pratinjau.' }}</p></div>@unless($quick)<x-ui.badge :status="$visit->status" />@endunless</div>
    @unless($quick)<nav class="step-nav" aria-label="Bagian editor"><a href="#dasar">Dasar</a><a href="#hidangan">Hidangan</a><a href="#ulasan">Ulasan</a><a href="#foto">Foto</a><a href="#publikasi">Pratinjau & publikasi</a></nav>@endunless
    <form method="post" action="{{ $quick ? url('admin/visit') : url('admin/visit/'.$visit->id) }}" class="stack" data-editor data-unsaved>@csrf
        @unless($quick)@method('PUT')<input type="hidden" name="editor" value="1"><input type="hidden" name="version" value="{{ old('version', $visit->version) }}">@endunless
        <input type="hidden" name="creation_token" value="{{ old('creation_token', $visit->creation_token ?? (string)\Illuminate\Support\Str::uuid()) }}">
        <section id="dasar" class="editor-section stack">
            @unless($quick)<h2>Dasar</h2>@endunless
            <div class="form-grid"><x-ui.combobox name="place_id" label="Tempat" kind="place" :selected="$visit->place_id ? ['id' => $visit->place_id, 'label' => $visit->place->name] : null" required />
                <x-ui.field name="visited_at" label="Tanggal & waktu (WIB)" type="datetime-local" :value="$visit->visited_at ? $visit->visited_at->setTimezone(config('foodie.timezone'))->format('Y-m-d\TH:i') : ($quick ? now()->setTimezone(config('foodie.timezone'))->format('Y-m-d\TH:i') : null)" help="Disimpan sebagai waktu UTC, ditampilkan di Asia/Jakarta." />
            </div>
            <x-ui.field name="title" label="Judul (opsional)" :value="$visit->title" data-slug-source />
            <x-ui.field name="experience" :label="$quick ? 'Catatan singkat (opsional)' : 'Cerita kunjungan'" :value="$visit->experience" textarea />
            <details><summary>Edit slug</summary><x-ui.field name="slug" label="Slug" :value="$visit->slug" data-slug-input :data-existing="$visit->exists ? 'true' : 'false'" help="Otomatis saat baru. Mengubah judul catatan tersimpan tidak mengubah slug. Slug lama diarahkan ke slug baru bila publik." /><small data-slug-preview></small></details>
        </section>
        @unless($quick)
        <section id="hidangan" class="editor-section"><h2>Hidangan yang dipesan</h2><p class="muted">Harga dan penilaian adalah catatan kunjungan ini.</p>
            <div data-items>@foreach(old('items', $rows) as $index => $row)@include('admin.visit.item', ['index' => $index, 'row' => $row])@endforeach</div>
            <x-ui.button type="button" data-add-item>Tambah hidangan</x-ui.button>
            <template data-item-template>@include('admin.visit.item', ['index' => '__INDEX__', 'row' => ['quantity' => 1]])</template>
        </section>
        <section id="ulasan" class="editor-section stack"><h2>Ulasan</h2>
            <div class="form-grid">@foreach(['service' => 'Pelayanan', 'atmosphere' => 'Suasana', 'cleanliness' => 'Kebersihan', 'value' => 'Sepadan dengan biaya'] as $key => $label)<div class="stack"><x-ui.rating-input :name="$key.'_rating'" :label="$label.' · rating'" :value="$visit->{$key.'_rating'}" /><x-ui.field :name="$key.'_review'" :label="$label.' · catatan'" :value="$visit->{$key.'_review'}" textarea /></div>@endforeach</div>
            <x-ui.field name="total_spent_idr" label="Total pengeluaran kunjungan (Rp)" type="number" min="0" step="1" :value="$visit->total_spent_idr" help="Dicatat terpisah dari harga hidangan. Kosong berbeda dari Rp0." />
            <x-ui.field name="conclusion" label="Kesimpulan" :value="$visit->conclusion" textarea />
            <div class="form-grid"><x-ui.combobox name="tags" label="Tag" kind="tag" :multiple="true" :selected="$visit->tags->map(fn ($term) => ['id' => $term->id, 'label' => $term->name])->all()" /><x-ui.combobox name="recommendations" label="Rekomendasi editorial" kind="recommendation" :multiple="true" :selected="$visit->recommendations->map(fn ($term) => ['id' => $term->id, 'label' => $term->name])->all()" /></div>
        </section>
        <section id="foto" class="editor-section stack"><h2>Foto & cover</h2><p class="muted">Simpan isian sebelum mengunggah foto. Cover dan foto hidangan memakai berkas galeri yang sama.</p>
            <label class="field">Cover<select name="cover_media_id"><option value="">Tanpa cover</option>@foreach($visit->media as $media)<option value="{{ $media->id }}" @selected(old('cover_media_id', $visit->cover_media_id) == $media->id)>Foto #{{ $media->id }} · {{ $media->alt_text }}</option>@endforeach</select></label>
        </section>
        <section id="publikasi" class="editor-section stack"><h2>Pratinjau & publikasi</h2><p>Publikasi memerlukan tempat, waktu, dan cerita atau hidangan. Rating, harga, dan foto boleh kosong.</p><div class="action-group"><x-ui.button href="{{ url('admin/visit/'.$visit->id.'/preview') }}">Pratinjau tersimpan</x-ui.button><x-ui.button type="submit" name="intent" value="private" data-confirm="Simpan privat? Kunjungan dan foto tidak akan tersedia untuk publik.">Simpan privat</x-ui.button><x-ui.button type="submit" name="intent" value="published" variant="primary" data-confirm="Publikasikan catatan tersimpan beserta perubahan ini?">Publikasikan</x-ui.button>@if($visit->status === 'published')<x-ui.button type="submit" name="intent" value="draft" data-confirm="Kembalikan ke draft? Akses publik dan foto akan ditutup pada permintaan berikutnya.">Kembali ke draft</x-ui.button>@endif</div></section>
        @endunless
        <div class="save-bar action-group"><x-ui.button href="{{ url('admin/visit') }}">Kembali</x-ui.button><x-ui.button type="submit" name="intent" :value="$quick ? 'draft' : 'save'" variant="primary">{{ $quick ? 'Simpan draft' : 'Simpan perubahan' }}</x-ui.button><span class="muted" data-save-state>Belum ada perubahan baru.</span></div>
    </form>
    @unless($quick)
        @include('admin.visit.media', ['visit' => $visit])
        <form method="post" action="{{ url('admin/visit/'.$visit->id) }}" class="section" data-confirm="Hapus kunjungan dari jurnal? Catatan dan foto dipertahankan untuk pemulihan, tetapi tidak dapat diakses publik.">@csrf @method('DELETE')<x-ui.button type="submit" variant="destructive">Hapus kunjungan</x-ui.button></form>
    @endunless
    <x-domain.inline-create />
</x-layout.admin>
