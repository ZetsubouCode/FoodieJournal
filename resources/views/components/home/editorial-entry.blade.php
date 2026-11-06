@props(['visit', 'lead' => false, 'recommendation' => false])
<article {{ $attributes->class(['archive-entry', 'archive-entry--lead' => $lead]) }}>
    <a class="archive-entry__link" href="{{ route('visit.show', $visit->slug) }}">
        <div class="archive-entry__photo"><x-domain.image :media="$visit->coverMedia" variant="card" :sizes="$lead ? '(min-width: 960px) 60vw, 100vw' : '(min-width: 960px) 28vw, 100vw'" /></div>
        <div class="archive-entry__copy">
            <x-domain.card-meta class="archive-entry__date" :date="$visit->visited_at" :location="$visit->place->city_name" />
            <h3>{{ $visit->display_title }}</h3><p class="archive-entry__place">{{ $visit->place->name }}</p>
            @if($recommendation)<p class="archive-entry__recommendation">{{ $visit->recommendations->pluck('name')->join(' · ') }}</p>@elseif($visit->overall_rating !== null)<p class="archive-entry__rating numeric">{{ \App\Support\Presentation::rating($visit->overall_rating) }} <span>/ 5 · Opini penulis</span></p>@endif
            @if($lead && ($recommendation ? $visit->conclusion : $visit->experience))<p class="archive-entry__excerpt">{{ \Illuminate\Support\Str::limit($recommendation ? $visit->conclusion : $visit->experience, 170) }}</p>@endif
        </div>
    </a>
</article>
