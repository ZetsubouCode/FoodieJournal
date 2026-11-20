@props(['place', 'allergenWarning' => false])
@php
    $photoVisit = $place->archivePhotoVisits->first();
    $photo = $photoVisit?->coverMedia?->variant('card') ? $photoVisit->coverMedia : $photoVisit?->media->first(fn ($image) => $image->variant('card'));
@endphp
<article class="journal-card"><a href="{{ url('place/'.$place->slug) }}">
    <x-domain.image :media="$photo" />
    <div><x-domain.card-meta :location="$place->city_name ?: 'Lokasi belum dicatat'" /><h3>{{ $place->name }}</h3><x-domain.rating :value="$place->food_score" label="Rata-rata rating hidangan" /><p class="metadata">{{ $place->rated_item_count }} hidangan dinilai dalam {{ $place->rated_visit_count }} kunjungan</p><x-domain.card-meta :date="$place->latest_visit_at" date-label="Terakhir" />
    @if($allergenWarning)<p class="muted">Kecocokan pada setidaknya satu hidangan. Informasi alergen mungkin belum tersedia.</p>@endif</div>
</a></article>
