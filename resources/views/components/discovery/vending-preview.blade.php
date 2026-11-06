@props(['entry' => null])
<article class="memory-showcase" data-memory-showcase aria-label="Pratinjau kunjungan">
    @if($entry)
        <a class="memory-showcase__surface" href="{{ $entry['href'] }}">
            <header class="memory-showcase__head"><div><p>Catatan kunjungan</p><h2 class="memory-showcase__collection">Kenangan tersimpan.</h2></div></header>
            <div class="memory-showcase__photo">
                @if($entry['image'])<img src="{{ $entry['image'] }}" width="{{ $entry['width'] }}" height="{{ $entry['height'] }}" alt="" data-vending-photo decoding="async">@endif
                <span class="memory-showcase__blank" data-photo-fallback @if($entry['image']) hidden @endif>Catatan tanpa foto</span>
            </div>
            <div class="memory-showcase__body"><h3>{{ $entry['title'] }}</h3><p class="memory-showcase__place">{{ $entry['place'] }}@if($entry['city']) · {{ $entry['city'] }}@endif</p><p class="memory-showcase__excerpt">{{ $entry['excerpt'] }}</p><span class="memory-showcase__hint">Baca kunjungan <span aria-hidden="true">↗</span></span></div>
        </a>
    @else
        <div class="memory-showcase__surface"><h2 class="memory-showcase__collection">Arsip masih kosong.</h2><p>Belum ada kunjungan yang dipublikasikan.</p><a href="{{ route('explore.index') }}">Jelajahi jurnal</a></div>
    @endif
</article>
