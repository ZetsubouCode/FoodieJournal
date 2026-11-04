<section class="section stack" id="unggah"><h2>Galeri tersimpan</h2>
    <form method="post" action="{{ url('admin/visit/'.$visit->id.'/media') }}" enctype="multipart/form-data" class="stack">@csrf
        <x-ui.field name="photo" label="Foto JPEG, PNG, atau WebP" type="file" accept="image/jpeg,image/png,image/webp" required help="Maksimum 15 MB dan 24 megapiksel. Original disimpan privat. Pratinjau memakai varian teroptimasi." />
        <x-ui.field name="alt_text" label="Deskripsi foto (tinjau sesuai isi foto)" maxlength="300" />
        <x-ui.button type="submit">Unggah foto</x-ui.button>
    </form>
    <div class="gallery">@foreach($visit->media as $media)<article class="stack">
        <x-domain.image :media="$media" />
        <p>Foto #{{ $media->id }} · {{ $media->processing_status }}</p>
        <form method="post" action="{{ url('admin/media/'.$media->id) }}">@csrf @method('PUT')<x-ui.field name="media_alt[{{ $media->id }}]" label="Deskripsi foto" :value="$media->alt_text" /><x-ui.button type="submit">Simpan deskripsi</x-ui.button></form>
        @if($media->processing_status !== 'ready')<x-ui.error-state title="Varian belum siap" :description="$media->processing_error ?: 'Original tetap disimpan. Coba proses ulang.'"><form method="post" action="{{ url('admin/media/'.$media->id.'/retry') }}">@csrf<x-ui.button type="submit">Proses ulang</x-ui.button></form></x-ui.error-state>@endif
        <form method="post" action="{{ url('admin/media/'.$media->id) }}" data-confirm="Hapus foto dari galeri dan lepaskan cover/tautan hidangan? Original dipertahankan dalam arsip privat.">@csrf @method('DELETE')<x-ui.button type="submit" variant="destructive">Hapus foto</x-ui.button></form>
    </article>@endforeach</div>
</section>
