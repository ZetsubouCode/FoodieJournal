@props(['variant' => 'secondary', 'size' => 'md', 'href' => null, 'type' => 'button', 'disabled' => false, 'loading' => false])
@if($href)
<a href="{{ $href }}" {{ $attributes->class(['btn', 'btn--'.$variant, 'btn--'.$size]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" @disabled($disabled || $loading || $type === 'button') @if($type === 'button') data-js-control @endif @if($disabled || $loading) data-preserve-disabled @endif @if($loading) aria-busy="true" @endif {{ $attributes->class(['btn', 'btn--'.$variant, 'btn--'.$size]) }}>{{ $slot }}</button>
@endif
