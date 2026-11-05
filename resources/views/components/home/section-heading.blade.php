@props(['id', 'eyebrow', 'title', 'description' => null, 'href' => null, 'action' => null])
<div class="home-section-heading">
    <div class="home-section-heading__copy">
        <p class="home-eyebrow">{{ $eyebrow }}</p>
        <h2 id="{{ $id }}">{{ $title }}</h2>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
    @if($href && $action)<a class="home-outline-link" href="{{ $href }}">{{ $action }} <span aria-hidden="true">↗</span></a>@endif
</div>
