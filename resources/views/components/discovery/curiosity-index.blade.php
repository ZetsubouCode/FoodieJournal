@props(['discovery', 'state'])
<section class="curiosity-index" aria-labelledby="curiosity-title">
    <header class="curiosity-index__intro"><div><p class="editorial-caption">The Curiosity Index</p><h1 id="curiosity-title">Mulai dari<br><em>rasa penasaran.</em></h1></div><p>Hidangan, tempat, dan cara memasak.<br>Temukan catatan dari sudut pandangmu.</p></header>
    <div class="curiosity-index__photos">
        @foreach(['food', 'city', 'style'] as $key)
            @php($tile = $discovery[$key])
            @php($active = $tile['value'] !== null && in_array($tile['value'], $state->get($tile['key'], []), true))
            @php($label = $tile['title'].($tile['detail'] ? ': '.$tile['detail'] : ''))
            <a class="curiosity-surface curiosity-surface--{{ $key }}" href="{{ $tile['url'] }}" data-curiosity-key="{{ $tile['key'] }}" data-curiosity-value="{{ $tile['value'] }}" data-curiosity-label="{{ $label }}" @if($active) data-selected="true" @endif aria-label="{{ $label.($active ? ' · Filter aktif' : '') }}">
                <x-domain.image :media="$tile['media']" variant="card" :priority="$loop->first" :sizes="$loop->first ? '(min-width: 1920px) 728px, (min-width: 768px) 42vw, calc(100vw - 2rem)' : '(min-width: 1920px) 560px, (min-width: 768px) 32vw, 50vw'" />
                <span class="curiosity-surface__copy"><span>{{ $tile['title'] }}</span><strong>{{ $tile['detail'] ?: 'Jelajahi catatan' }}</strong><span aria-hidden="true">↗</span></span>
            </a>
        @endforeach
    </div>
    <nav class="curiosity-index__directory" aria-label="Cara lain menjelajah">
        @foreach(['type', 'recommendation'] as $key)
            @php($tile = $discovery[$key])
            @if($tile['detail'])
                @php($active = in_array($tile['value'], $state->get($tile['key'], []), true))
                <a href="{{ $tile['url'] }}" data-curiosity-key="{{ $tile['key'] }}" data-curiosity-value="{{ $tile['value'] }}" data-curiosity-label="{{ $tile['title'].': '.$tile['detail'] }}" @if($active) data-selected="true" @endif aria-label="{{ $tile['title'].': '.$tile['detail'].($active ? ' · Filter aktif' : '') }}"><span>{{ $tile['title'] }}</span><strong>{{ $tile['detail'] }}</strong><span aria-hidden="true">↗</span></a>
            @else
                <p><span>{{ $tile['title'] }}</span><strong>{{ $key === 'recommendation' ? 'Belum ada rekomendasi yang dipublikasikan' : 'Jenis hidangan belum dicatat' }}</strong></p>
            @endif
        @endforeach
    </nav>
</section>
