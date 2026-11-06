{{-- Compatibility for existing collection clients. The playable cabinet lives on Home. --}}
<div data-home-archive data-selected-slot="{{ $memory['slot'] }}" data-selected-label="{{ $memory['label'] }}" data-memory-count="{{ $memory['count'] }}" data-mode="{{ $state->get('tab') }}" data-reset-url="{{ $resetUrl }}">
    <nav data-memory-slots aria-label="Koleksi arsip">
        @foreach($slots as $slot)
            @if($slot['url'])<a data-memory-slot="{{ $slot['id'] }}" href="{{ $slot['url'] }}">@if($slot['image'])<img src="{{ $slot['image'] }}" alt="" width="360" height="260">@endif{{ $slot['label'] }}</a>@endif
        @endforeach
    </nav>
    <x-discovery.showcase :memory="$memory" :state="$state" />
</div>
