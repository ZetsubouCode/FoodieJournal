@props(['discovery'])
<section class="curiosity-index" aria-labelledby="curiosity-title">
    <header class="curiosity-index__intro"><div><p class="editorial-caption">The Curiosity Index</p><h1 id="curiosity-title">Mulai dari<br><em>rasa penasaran.</em></h1></div><p>Hidangan, tempat, dan cara memasak.<br>Temukan catatan dari sudut pandangmu.</p></header>
    <div class="curiosity-index__photos">
        @foreach(['food', 'city', 'style'] as $key)
            @php($tile = $discovery[$key])
            <a class="curiosity-surface curiosity-surface--{{ $key }}" href="{{ $tile['url'] }}" aria-label="{{ $tile['title'].($tile['detail'] ? ': '.$tile['detail'] : '') }}">
                <x-domain.image :media="$tile['media']" variant="card" :priority="$loop->first" sizes="(min-width: 960px) 45vw, 100vw" />
                <span class="curiosity-surface__copy"><span>{{ $tile['title'] }}</span><strong>{{ $tile['detail'] ?: 'Jelajahi catatan' }}</strong><span aria-hidden="true">↗</span></span>
            </a>
        @endforeach
    </div>
    <nav class="curiosity-index__directory" aria-label="Cara lain menjelajah">
        @foreach(['type', 'recommendation'] as $key)
            @php($tile = $discovery[$key])
            <a href="{{ $tile['url'] }}"><span>{{ $tile['title'] }}</span><strong>{{ $tile['detail'] ?: 'Semua catatan' }}</strong><span aria-hidden="true">↗</span></a>
        @endforeach
    </nav>
</section>
