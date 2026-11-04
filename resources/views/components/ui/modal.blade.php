@props(['id', 'title', 'description' => null])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" @if($description) aria-describedby="{{ $id }}-description" @endif {{ $attributes }}>
    <div class="dialog-head"><h2 id="{{ $id }}-title">{{ $title }}</h2><x-ui.button type="button" variant="tertiary" data-dialog-close aria-label="Tutup dialog">×</x-ui.button></div>
    @if($description)<p id="{{ $id }}-description" class="muted">{{ $description }}</p>@endif
    {{ $slot }}
</dialog>
