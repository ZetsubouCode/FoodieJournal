@props(['memory'])
<article class="memory-showcase" data-screen-kind="memory" data-memory-object="{{ $memory['object_id'] }}" aria-label="Kenangan terungkap">
    @if($memory['count'])
    <a class="memory-showcase__surface" href="{{ $memory['href'] }}" aria-label="Buka {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$memory['mode']] }}: {{ $memory['title'] }}">
        <header class="memory-showcase__head"><div><p>Kenangan terungkap</p><h2 class="memory-showcase__collection">{{ ['visit' => 'Catatan kunjungan', 'food' => 'Hidangan yang dicoba', 'place' => 'Tempat yang diingat'][$memory['mode']] }}</h2></div></header>
        <div class="memory-showcase__photo">
            @if($memory['image'])<img src="{{ $memory['image'] }}" srcset="{{ $memory['srcset'] }}" sizes="(min-width: 960px) 30vw, 100vw" alt="{{ $memory['alt'] }}" width="{{ $memory['width'] }}" height="{{ $memory['height'] }}" data-memory-photo>@endif
            <div class="memory-showcase__blank" data-memory-photo-fallback @if($memory['image']) hidden @endif><span>Foto belum tersedia untuk catatan ini</span></div>
        </div>
        <div class="memory-showcase__body"><h3>{{ $memory['title'] }}</h3><p class="memory-showcase__place">{{ $memory['place'] }} @if($memory['city']) · {{ $memory['city'] }}@endif</p>@if($memory['excerpt'])<p class="memory-showcase__excerpt">{{ $memory['excerpt'] }}</p>@endif<span class="memory-showcase__hint">Buka {{ ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$memory['mode']] }} <span aria-hidden="true">↗</span></span></div>
    </a>
    @else<p>Tidak ada kenangan yang cocok.</p>@endif
</article>
