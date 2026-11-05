@props(['admin' => false])
<header class="site-header container" id="top">
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
            <a href="{{ route('explore.index') }}" @if(request()->routeIs('explore.index')) aria-current="page" @endif>Jelajah</a>
        </nav>
    @endif
</header>
