@php
    $page = config('error-pages.'.$code);
    $canRetry = request()->isMethod('GET') || request()->isMethod('HEAD');
    $primary = $page['primary'];
    $secondary = $page['secondary'];
    if ($code === 500 || $code === 503) {
        $primary = $canRetry ? ['label' => 'Coba lagi', 'url' => request()->fullUrl()] : ['label' => 'Ke beranda', 'url' => url('/')];
        $secondary = $canRetry ? ['label' => 'Ke beranda', 'url' => url('/')] : ['label' => 'Jelajahi catatan', 'url' => url('/explore')];
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111211">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} · {{ $page['heading'] }} · Foodie</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/error.css') }}">
</head>
<body class="error-page">
    <a class="error-skip" href="#error-main">Lewati ke isi</a>
    <div class="error-scene">
        <header class="error-header">
            <a class="error-brand" href="{{ url('/') }}" aria-label="Foodie — beranda">Foodie<span class="error-brand-dot" aria-hidden="true">.</span></a>
            <span class="error-header-label">Jurnal kuliner</span>
        </header>
        <main id="error-main" class="error-main">
            <div class="error-copy">
                <p class="error-eyebrow"><span class="error-eyebrow-line" aria-hidden="true"></span>{{ $page['eyebrow'] }}</p>
                <p class="error-code numeric" aria-label="Kesalahan {{ $code }}"><span class="error-code-text">{{ $code }}</span><svg class="error-stroke" viewBox="0 0 520 90" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M6 64C88 43 172 46 254 34C336 21 427 24 514 6" /><path d="M46 76C166 52 305 39 471 27" /></svg></p>
                <h1>{{ $page['heading'] }}</h1>
                <p class="error-description">{{ $page['description'] }}</p>
                <nav class="error-actions" aria-label="Langkah selanjutnya">
                    <a class="error-action error-action--primary" href="{{ $primary['url'] }}">{{ $primary['label'] }} <span aria-hidden="true">↗</span></a>
                    <a class="error-action error-action--secondary" href="{{ $secondary['url'] }}">{{ $secondary['label'] }}</a>
                </nav>
            </div>
        </main>
        <footer class="error-footer"><span>Foodie / Catatan perjalanan rasa</span><span class="error-footer-code" aria-hidden="true">{{ $code }} — {{ $page['footer'] }}</span></footer>
    </div>
</body>
</html>
