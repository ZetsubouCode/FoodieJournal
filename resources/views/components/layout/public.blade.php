@props(['title' => 'Foodie', 'description' => 'Catatan kunjungan, hidangan, dan pengalaman makan dari satu penulis.', 'canonical' => null, 'noindex' => false, 'image' => null, 'admin' => false])
@php($errors = $errors ?? new \Illuminate\Support\ViewErrorBag)
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#111211">
    <title>{{ $title }}{{ $title === 'Foodie' ? '' : ' · Foodie' }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $noindex || $admin ? 'noindex,follow' : 'index,follow' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:title" content="{{ $title }}"><meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website"><meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if($image && !$noindex)<meta property="og:image" content="{{ $image }}">@endif
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Lewati ke isi</a>
    <x-site.header :admin="$admin" />
    <main id="main" class="container" tabindex="-1">
        @if(session('success'))<x-ui.alert severity="success" title="Tersimpan">{{ session('success') }}</x-ui.alert>@endif
        @if($errors->any())
            <x-ui.alert severity="error" title="Periksa kembali isian" id="error-summary" tabindex="-1" data-error-summary>
                <ul>@foreach($errors->getMessages() as $field => $messages)<li><a href="#{{ str_replace(['.', '[', ']'], '-', $field) }}">{{ $messages[0] }}</a></li>@endforeach</ul>
            </x-ui.alert>
        @endif
        {{ $slot }}
    </main>
    <x-site.footer />
    <x-ui.toast-container />
    <x-ui.modal id="confirm-dialog" title="Konfirmasi tindakan" description="Perubahan ini memengaruhi catatan dan visibilitasnya.">
        <p data-confirm-message></p>
        <div class="action-group"><x-ui.button type="button" data-dialog-close>Batal</x-ui.button><x-ui.button type="button" variant="destructive" data-confirm-accept>Lanjutkan</x-ui.button></div>
    </x-ui.modal>
</body>
</html>
