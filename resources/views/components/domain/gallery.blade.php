@props(['media'])
<div class="gallery">
    @foreach($media as $photo)
        <x-domain.photo-link
            :photo="$photo"
            :label="'Lihat foto '.$loop->iteration.($photo->alt_text ? ': '.$photo->alt_text : '')"
            :sizes="$loop->count === 1 ? '(min-width: 2560px) 2432px, 94vw' : '(min-width: 2560px) 1400px, (min-width: 768px) 55vw, calc(100vw - 2rem)'"
            data-view-photo="{{ $photo->uuid }}"
            data-original="{{ url('media/'.$photo->uuid.'/original') }}"
            data-alt="{{ $photo->alt_text ?? '' }}"
        />
    @endforeach
</div>

<x-ui.modal id="photo-viewer" title="Foto kunjungan" class="viewer" data-photo-viewer>
    <p role="status" data-viewer-status></p>
    <div class="viewer-stage" data-viewer-stage aria-busy="false"><img alt="" data-viewer-image hidden></div>
    <div class="action-group">
        <x-ui.button type="button" data-viewer-prev>Sebelumnya</x-ui.button>
        <x-ui.button type="button" data-viewer-next>Berikutnya</x-ui.button>
        <x-ui.button type="button" data-viewer-zoom aria-pressed="false" disabled>Perbesar foto</x-ui.button>
        <x-ui.button type="button" data-viewer-fullscreen aria-pressed="false">Layar penuh</x-ui.button>
        <x-ui.button type="button" data-viewer-retry hidden>Coba lagi</x-ui.button>
    </div>
</x-ui.modal>
