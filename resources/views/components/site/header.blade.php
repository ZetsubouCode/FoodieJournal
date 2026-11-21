@props(['admin' => false, 'visitPlaceUrl' => null])
<header class="site-header{{ $admin ? ' container' : '' }}" id="top">
    @unless($admin)<div class="container site-header__inner">@endunless
    <a href="{{ url('/') }}" class="brand" aria-label="Foodie, beranda">Foodie<span aria-hidden="true">.</span></a>
    @if($admin && auth()->check())
        <nav aria-label="Navigasi admin" class="admin-nav">
            @foreach(['admin' => 'Ringkasan', 'admin/visit' => 'Kunjungan', 'admin/place' => 'Tempat', 'admin/taxonomy' => 'Taksonomi'] as $path => $label)
                <a href="{{ url($path) }}" @if(request()->path() === $path) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            <form method="post" action="{{ url('admin/logout') }}">@csrf<x-ui.button type="submit" variant="tertiary">Keluar</x-ui.button></form>
        </nav>
    @else
        <nav aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('explore.index') }}" @if(!$visitPlaceUrl && request()->routeIs('explore.index', 'place.show')) aria-current="page" @endif>Jelajah</a>
        </nav>
    @endif
    @if($visitPlaceUrl)
        <nav class="site-header__context" aria-label="Navigasi catatan">
            <a href="#main" aria-current="page">Catatan ini</a>
            <a href="{{ $visitPlaceUrl }}">Tentang tempat</a>
            <button class="site-header__motion" type="button" role="switch" aria-checked="false" data-visit-motion-toggle hidden>
                <span>Gerak</span> <span aria-hidden="true" data-visit-motion-state>nonaktif</span>
            </button>
        </nav>
    @endif
    @unless($admin)</div>@endunless
</header>
