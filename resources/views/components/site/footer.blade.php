<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__story">
            <p class="site-footer__eyebrow">Catatan penutup</p>
            <p class="site-footer__statement">Rasa boleh lewat.<br><span>Ceritanya tinggal.</span></p>
            <p class="site-footer__description">Foodie. — jurnal pribadi tentang tempat, hidangan, dan kunjungan yang ingin diingat kembali.</p>
        </div>
        <nav class="site-footer__nav" aria-label="Navigasi kaki halaman">
            <p>Lanjut jelajah</p>
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('explore.index') }}">Jelajah</a>
            <a class="site-footer__top" href="#top" aria-label="Kembali ke atas">↑ <span>Kembali ke atas</span></a>
        </nav>
    </div>
    <p class="container site-footer__copyright">© {{ now()->year }} Foodie.</p>
</footer>
