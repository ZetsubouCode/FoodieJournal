@props(['date' => null, 'location' => null, 'dateLabel' => null])
<div {{ $attributes->class('record-meta') }}>
    @if($date)<time class="record-meta__item" datetime="{{ \Carbon\CarbonImmutable::parse($date)->toIso8601String() }}"><x-ui.meta-icon name="calendar" /><span>@if($dateLabel){{ $dateLabel }} @endif{{ \App\Support\Presentation::localTime($date, 'd M Y') }}</span></time>@endif
    @if($location)<span class="record-meta__item"><x-ui.meta-icon name="location" /><span>{{ $location }}</span></span>@endif
</div>
