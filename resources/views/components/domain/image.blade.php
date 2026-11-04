@props(['media' => null, 'variant' => 'card', 'priority' => false])
@php($image = $media?->variant($variant))
@if($image)
<img src="{{ url('media/'.$media->uuid.'/'.$variant) }}" srcset="@foreach($media->variants->where('status', 'ready')->unique('width') as $v){{ url('media/'.$media->uuid.'/'.$v->variant) }} {{ $v->width }}w{{ !$loop->last ? ', ' : '' }}@endforeach" sizes="{{ $priority ? '(min-width: 768px) 55vw, 100vw' : '(min-width: 1024px) 33vw, (min-width: 550px) 50vw, 100vw' }}" width="{{ $image->width }}" height="{{ $image->height }}" alt="{{ $media->alt_text ?? '' }}" loading="{{ $priority ? 'eager' : 'lazy' }}" @if($priority) fetchpriority="high" @endif decoding="async" {{ $attributes->class('media-image') }}>
@else
<div {{ $attributes->class('photo-fallback') }}>Foto belum tersedia</div>
@endif
