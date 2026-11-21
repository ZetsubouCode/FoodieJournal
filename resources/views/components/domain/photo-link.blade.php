@props(['photo', 'label', 'sizes'])
@php
    $variant = $photo->variant('card') ? 'card' : 'thumbnail';
    $derivative = url('media/'.$photo->uuid.'/'.$variant);
@endphp
<a href="{{ $derivative }}" aria-label="{{ $label }}" {{ $attributes->class('dossier-photo') }}>
    <x-domain.image :media="$photo" :variant="$variant" :sizes="$sizes" :derivatives="['card', 'thumbnail']" />
    <span class="dossier-photo__corners" aria-hidden="true"></span>
    <span class="dossier-photo__icon"><x-ui.meta-icon name="focus" /></span>
</a>
