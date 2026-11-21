@props(['admin' => false, 'visitPlaceUrl' => null, 'visitHasTasting' => false])
<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__story">
            <p class="site-footer__statement">Rasa boleh lewat.<br><span>Ceritanya tinggal.</span></p>
            <p class="site-footer__description">Foodie. — jurnal pribadi tentang {{ $admin ? 'tempat, hidangan, dan kunjungan' : 'makanan, tempat, dan pengalaman' }} yang ingin diingat kembali.</p>
        </div>
        <nav class="site-footer__nav" aria-label="Navigasi kaki halaman">
            @if($admin)
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('explore.index') }}">Jelajah</a>
                <a class="site-footer__top" href="#top" aria-label="Kembali ke atas">↑ <span>Kembali ke atas</span></a>
            @else
                <a href="#top">Kembali ke atas</a>
                @if($visitPlaceUrl)
                    @if($visitHasTasting)<a href="#tasting">Lihat hidangan</a>@endif
                    <a href="{{ $visitPlaceUrl }}">Tentang tempat</a>
                @else
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('explore.index') }}">Jelajah</a>
                @endif
            @endif
        </nav>
        <p class="site-footer__copyright">© {{ now()->year }} Foodie.{{ $admin ? '' : ' — Sebuah arsip pribadi.' }}</p>
    </div>
</footer>
