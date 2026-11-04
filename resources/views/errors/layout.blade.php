<x-layout.public :title="$code.' · '.$heading" :noindex="true">
    <div class="section reading"><p class="muted numeric">Foodie · {{ $code }}</p><h1>{{ $heading }}</h1><p>{{ $message }}</p>
        <div class="action-group"><x-ui.button :href="$recovery" variant="primary">{{ $action }}</x-ui.button><x-ui.button href="{{ url('/') }}">Ke beranda</x-ui.button></div>
    </div>
</x-layout.public>
