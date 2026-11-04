@props(['value' => null, 'label' => null])
<span class="rating">@if($label){{ $label }}: @endif{{ \App\Support\Presentation::rating($value) }}@if($value !== null)<span class="muted"> / 5</span>@endif</span>
