@props(['value' => null, 'label' => null, 'stars' => false])
@php($formatted = \App\Support\Presentation::rating($value))
@if($stars)
    <span @class(['rating', 'rating--editorial', 'rating--unrated' => $value === null])>
        <span class="sr-only">{{ $label ? $label.': ' : '' }}{{ $formatted }}{{ $value !== null ? ' dari 5' : '' }}</span>
        <span class="rating__value" aria-hidden="true">{{ $formatted }}@if($value !== null)<small> / 5</small>@endif</span>
        @if($value !== null)
            @php($halfSteps = (int) round($value * 2))
            <span class="rating__stars" aria-hidden="true">
                @for($star = 1; $star <= 5; $star++)
                    <span @class(['rating__star', 'rating__star--full' => $halfSteps >= $star * 2, 'rating__star--half' => $halfSteps === $star * 2 - 1])>★</span>
                @endfor
            </span>
        @endif
    </span>
@else
    <span class="rating">@if($label){{ $label }}: @endif{{ $formatted }}@if($value !== null)<span class="muted"> / 5</span>@endif</span>
@endif
