@props(['tile', 'class' => ''])
<a class="discovery-tile {{ $class }}" href="{{ $tile['url'] }}" aria-label="Jelajahi {{ $tile['title'] }}{{ $tile['detail'] ? ': '.$tile['detail'] : '' }}">
    <x-domain.image :media="$tile['media']" sizes="(min-width: 768px) 50vw, 100vw" />
    <span class="discovery-tile__copy"><span class="discovery-tile__eyebrow">{{ $tile['eyebrow'] }}</span><strong>{{ $tile['title'] }}</strong>@if($tile['detail'])<span class="discovery-tile__detail">{{ $tile['detail'] }}</span>@endif</span>
    <span class="discovery-tile__arrow" aria-hidden="true">↗</span>
</a>
