@props(['memory', 'state'])
@php($noun = ['visit' => 'kunjungan', 'food' => 'hidangan', 'place' => 'tempat'][$state->get('tab')])
<article id="memory-showcase" class="memory-showcase" data-memory-showcase data-screen-kind="collection" aria-label="Koleksi arsip">
    <a class="memory-showcase__surface" href="{{ $state->url() }}" aria-label="Lihat {{ $memory['count'] }} {{ $noun }} dalam {{ $memory['label'] }}">
        <header class="memory-showcase__head"><div><p>{{ $memory['code'] ? 'Koleksi '.$memory['code'] : 'Arsip pengalaman' }}</p><h2 class="memory-showcase__collection">{{ $memory['label'] }}</h2></div><span class="numeric">{{ str_pad((string) $memory['count'], 2, '0', STR_PAD_LEFT) }}<small>{{ $noun }}</small></span></header>
        <div class="memory-showcase__photo">
            @if($memory['image'])<img src="{{ $memory['image'] }}" srcset="{{ $memory['srcset'] }}" sizes="(min-width: 960px) 30vw, 100vw" alt="" width="{{ $memory['width'] }}" height="{{ $memory['height'] }}" data-memory-photo fetchpriority="high">@endif
            <div class="memory-showcase__blank" data-memory-photo-fallback @if($memory['image']) hidden @endif><span>{{ $memory['count'] ? 'Arsip catatan' : 'Belum ada catatan yang cocok.' }}</span></div>
        </div>
        <div class="memory-showcase__body"><span class="memory-showcase__hint">Jelajahi {{ $memory['count'] }} {{ $noun }} <span aria-hidden="true">↗</span></span></div>
    </a>
</article>
