<div class="discovery home-archive" data-home-archive data-page-url="{{ route('home') }}">
    <section class="dispenser-scene" aria-labelledby="discovery-title" data-discovery-scene>
        <header class="discovery-intro"><div><p class="discovery-kicker">Memory Archive</p><h1 id="discovery-title">Rasa berlalu.<br><em>Kenangannya tinggal.</em></h1></div></header>
        <div class="dispenser-scene__machine"><x-discovery.dispenser :vending="$vending" /></div>
    </section>
    <div class="discovery-error alert alert--error" role="alert" data-discovery-error hidden><span>Halaman belum dapat dimuat. Pilihan sebelumnya tetap tersedia.</span><button type="button" class="btn" data-discovery-retry>Coba lagi</button></div>
    <noscript><p>Semua jendela menautkan kunjungan. <a href="{{ route('explore.index') }}">Jelajahi seluruh catatan</a>.</p></noscript>
</div>
