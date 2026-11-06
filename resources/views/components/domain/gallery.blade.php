@props(['media'])
@if($media->isNotEmpty())
<div class="gallery">@foreach($media as $photo)@if($photo->variant('thumbnail'))
    <button type="button" disabled data-js-control data-view-photo data-original="{{ url('media/'.$photo->uuid.'/original') }}" data-fallback="{{ url('media/'.$photo->uuid.'/card') }}" data-alt="{{ $photo->alt_text ?? '' }}" aria-label="Perbesar foto {{ $loop->iteration }}{{ $photo->alt_text ? ': '.$photo->alt_text : '' }}"><x-domain.image :media="$photo" :variant="$photo->variant('card') ? 'card' : 'thumbnail'" sizes="(min-width: 960px) 60vw, 100vw" /></button>
@endif @endforeach</div>
<x-ui.modal id="photo-viewer" title="Foto kunjungan" description="Original berkualitas penuh. Gunakan panah kiri/kanan untuk berpindah foto." class="viewer" data-photo-viewer>
    <p role="status" data-viewer-status></p><img alt="" data-viewer-image hidden>
    <div class="action-group"><x-ui.button type="button" data-viewer-prev>Sebelumnya</x-ui.button><x-ui.button type="button" data-viewer-next>Berikutnya</x-ui.button><x-ui.button type="button" data-viewer-retry hidden>Coba lagi</x-ui.button><x-ui.button type="button" data-viewer-zoom>Perbesar / sesuaikan</x-ui.button></div>
</x-ui.modal>
@endif
