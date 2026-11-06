@props(['vending'])
<nav class="memory-slots archive-position" aria-label="Kunjungan dalam kabinet" data-memory-slots>
    @for($index = 0; $index < 6; $index++)
        @php($entry = $vending['entries'][$index] ?? null)
        @if($entry)
            <a class="memory-slot" href="{{ $entry['href'] }}" data-memory-slot="{{ $entry['id'] }}" @if($index === 0) aria-current="true" @endif>
                <span class="memory-slot__glass">
                    @if($entry['image'])<img src="{{ $entry['image'] }}" alt="" width="{{ $entry['width'] }}" height="{{ $entry['height'] }}" data-vending-photo decoding="async">@endif
                    <span class="memory-slot__archive-mark" data-photo-fallback @if($entry['image']) hidden @endif><svg viewBox="0 0 100 80" aria-hidden="true"><path d="M12 23h76v48H12zM12 24l38 26 38-26M24 14h52v9"/></svg><span>Catatan tanpa foto</span></span>
                    <span class="memory-slot__code">{{ $entry['code'] }}</span>
                </span>
                <span class="memory-slot__name">{{ $entry['place'] }}</span><span class="sr-only">{{ $entry['title'] }}</span>
                <template data-visit-preview><x-discovery.vending-preview :entry="$entry" /></template>
                <template data-visit-print><a class="vending-print" href="{{ $entry['href'] }}" aria-label="Ambil foto: {{ $entry['title'] }}" data-printed-visit="{{ $entry['id'] }}"><span class="vending-print__photo">@if($entry['image'])<img src="{{ $entry['image'] }}" width="{{ $entry['width'] }}" height="{{ $entry['height'] }}" alt="" data-vending-photo>@endif<span data-photo-fallback @if($entry['image']) hidden @endif>Catatan tanpa foto</span></span><span class="vending-print__caption">{{ $entry['place'] }}<small>{{ $entry['date'] }}</small></span></a></template>
            </a>
        @else
            <span class="memory-slot memory-slot--empty"><span class="memory-slot__glass"><span class="memory-slot__archive-mark">{{ $vending['total'] ? 'Akhir halaman' : 'Belum ada catatan' }}</span></span><span class="memory-slot__name">—</span></span>
        @endif
    @endfor
</nav>
