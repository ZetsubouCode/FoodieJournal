@props(['vending'])
<div class="memory-machine vending-machine" data-machine data-page="{{ $vending['page'] }}" data-pages="{{ $vending['pages'] }}" data-total="{{ $vending['total'] }}" aria-label="Memory Archive">
    <img class="memory-machine__shell" src="{{ asset('assets/explore/archive/cabinet-v2.webp') }}" width="1634" height="963" alt="" fetchpriority="high" aria-hidden="true">
    <img class="vending-rail" src="{{ asset('assets/vending/rail.svg') }}" width="140" height="840" alt="" aria-hidden="true">
    <div class="memory-machine__marquee archive-position"><span>Arsip rasa</span><span>Nº <span data-page-number>{{ str_pad((string) $vending['page'], 2, '0', STR_PAD_LEFT) }}</span><small>Koleksi pribadi</small></span></div>
    <x-discovery.vending-slots :vending="$vending" />
    <div class="memory-machine__display archive-position">
        <button class="memory-machine__step" type="button" data-vending-choose disabled aria-label="Pilih kunjungan berikutnya"><i aria-hidden="true"></i><span>Pilih koleksi</span></button>
        <button class="memory-machine__step" type="button" data-vending-print disabled aria-label="Cetak foto kunjungan terpilih"><i aria-hidden="true"></i><span>Ambil memori</span></button>
        <button class="memory-machine__step" type="button" data-vending-story disabled aria-label="Baca cerita kunjungan terpilih"><i aria-hidden="true"></i><span>Baca cerita</span></button>
    </div>
    <div class="memory-machine__screen archive-position" data-vending-screen><x-discovery.vending-preview :entry="$vending['entries'][0] ?? null" /></div>
    <div class="memory-tray archive-position vending-tray" data-memory-tray><div class="vending-paper-path" data-paper-path></div><span class="vending-roller" aria-hidden="true"></span></div>
    <span class="memory-tray__lip archive-position" aria-hidden="true"></span>
    <div class="vending-hardware">
        <button type="button" class="vending-socket" data-lamp-socket aria-label="Pasang lampu pada soket" aria-describedby="lamp-help" disabled><img src="{{ asset('assets/vending/socket.svg') }}" width="66" height="280" alt=""><span class="vending-socket__ghost" aria-hidden="true"></span></button>
        <button type="button" class="vending-cradle" data-lamp-cradle aria-label="Kembalikan lampu ke dudukan untuk mematikan mesin" disabled><img src="{{ asset('assets/vending/cradle.svg') }}" width="260" height="80" alt=""></button>
        <button type="button" class="vending-lamp" data-vending-lamp aria-label="Angkat lampu, lalu pilih soket untuk memasangnya" aria-describedby="lamp-help" aria-pressed="false" disabled><img src="{{ asset('assets/vending/tube.svg') }}" width="42" height="238" alt="" draggable="false"><span class="vending-lamp__light" aria-hidden="true"></span></button>
        <div class="vending-rotary-wrap"><button type="button" class="archive-knob vending-rotary" data-vending-knob disabled aria-label="Putar halaman kunjungan. Panah kiri atau kanan untuk berpindah." aria-describedby="knob-help"><img class="vending-rotary__rotor" src="{{ asset('assets/vending/rotary.svg') }}" width="100" height="100" alt="" draggable="false"></button><span class="vending-rotary__legend" aria-hidden="true">− &nbsp; +</span></div>
        <button type="button" class="vending-reset" data-memory-reset aria-label="Reset ke halaman pertama dan bersihkan foto cetak" disabled><img src="{{ asset('assets/vending/reset-case.svg') }}" width="100" height="100" alt=""><img class="vending-reset__face" src="{{ asset('assets/vending/reset-face.svg') }}" width="100" height="100" alt=""><span class="vending-reset__label">Reset</span></button>
        <div class="vending-page-actions"><button type="button" data-vending-previous disabled aria-label="Halaman kunjungan sebelumnya">←</button><button type="button" data-vending-next disabled aria-label="Halaman kunjungan berikutnya">→</button></div>
        <p class="vending-hint" data-vending-hint hidden>Lampunya belum terpasang.</p>
    </div>
    <p id="lamp-help" class="sr-only">Seret lampu dari dudukan ke soket, lalu lepaskan. Atau tekan lampu lalu tekan soket. Untuk mematikan, kembalikan ke dudukan. Escape membatalkan.</p>
    <p id="knob-help" class="sr-only">Putar kenop atas ke dua arah untuk halaman. Tombol bawah ditekan untuk reset. Cetak foto melalui Ambil memori; foto yang keluar membuka kunjungan.</p>
</div>
<p class="sr-only" role="status" aria-live="polite" aria-atomic="true" data-machine-status>Pasang lampu untuk menyalakan mesin. Semua catatan juga tersedia melalui Jelajah.</p>


