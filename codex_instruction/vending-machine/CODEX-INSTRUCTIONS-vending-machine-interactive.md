# CODEX IMPLEMENTATION INSTRUCTIONS — FOODIE PLAYABLE VENDING MACHINE

## ROLE, OBJECTIVE, AND DEFINITION OF DONE

Bertindak sekaligus sebagai **senior Laravel/frontend engineer, creative UI/UX director, professional game/interaction designer, illustrator/asset designer, motion designer, accessibility engineer, dan QA**. Ini tugas **implementasi**, bukan hanya audit, proposal, atau mockup. Inspeksi repository terlebih dahulu, lalu lakukan perubahan nyata, uji, dan perbarui dokumentasi.

Ubah vending machine pada **homepage Foodie** menjadi antarmuka eksplorasi kuliner yang terasa seperti **mesin mekanis fisik yang dapat dimainkan**. Visual target mengikuti screenshot referensi di `reference/vending-machine-original-reference.png` (pastikan referensi ini tersedia di repo bersama instruksi; jika Codex dijalankan dari root dan file dipindahkan, sesuaikan path). **Pertahankan arah art direction:** dark cinematic editorial, mesin logam vintage bertekstur, kuningan/copper, warm ivory, cahaya amber, kedalaman perspektif, asymmetric composition. Ini jurnal kuliner pribadi, bukan aplikasi memasak, restoran, atau penjualan makanan.

**Keberhasilan bukan sekadar semua elemen bisa diklik.** Animasi harus halus, state saling konsisten, kontrol mempunyai affordance, asset terlihat rapi dan sekelas ilustrasi produksi, dan navigasi asli ke konten terbit tetap mudah diakses. Jangan mengklaim fitur, ukuran performa, atau tes berhasil tanpa benar-benar memverifikasinya.

## 0. REPOSITORY INSPECTION — WAJIB SEBELUM MENGUBAH KODE

1. Inspeksi stack dan struktur repository: Blade homepage, controller/route, komponen bersama, Vite, CSS/JS, gambar/asset vending machine, model dan sumber data `place`, `visit`, `visit_item`, published scopes, test suite, dokumen design system, dan pola animasi yang sudah ada.
2. Temukan semua elemen UI screenshot yang sekarang sudah diimplementasikan: lampu kiri, dua kontrol bundar di kiri, 6 kartu/slot (3 kolom × 2 baris), panel preview kanan, tiga tombol indikator kanan, plat nomor, dispenser kartu bawah. Petakan DOM, sumber gambar, click handler, state, dan area yang saat ini hardcoded.
3. Inspeksi secara visual kondisi aktual dengan preview/browser/screenshot **jika lingkungan menyediakan akses**; jangan mengaku sudah melihat browser bila tidak tersedia. Bandingkan dengan screenshot referensi serta design tokens project.
4. Inventarisasi asset: mana background komposit/flattened dan mana layer transparan. Jangan menumpuk kontrol baru di atas kontrol lama; ganti/bersihkan yang usang. Bila sumber ilustrasi layered tersedia, gunakan dan turunkan versi web yang optimal.
5. Tentukan rencana modifikasi file minimal yang menjaga fungsi existing dan kompatibilitas Explore/Visit/Place. **Jangan redesign unrelated pages, jangan tambah SPA, Three.js, game engine, paket animasi berat, atau dependency berbayar.** Gunakan Laravel Blade, CSS, Vite/JS modular, Pointer Events dan Web Animations API bila sesuai.

## 1. TARGET VISUAL DAN ASSET DESIGN (SAMA PENTING DENGAN LOGIKA)

### 1.1 Komposisi mesin
- Jadikan screenshot acuan untuk proporsi, bahasa material dan hierarki; **jangan render seluruh screenshot sebagai satu gambar lalu berpura-pura elemen di atasnya interaktif**.
- Pertahankan mesin besar yang dominan dalam hero, tekstur brushed metal/aged brass, kaca, refleksi ambient, bevel, rivet, recessed socket, panel display kiri dan kanan. Kedalaman perspektif harus meyakinkan dan tertata, bukan tampilan dashboard datar/generic AI-slop.
- Atur framing sedikit jika perlu agar lampu lepas beserta cradle terlihat tanpa memotong mesin, menimpa hero lain, atau menciptakan horizontal scrolling. Komposisi desktop tetap kuat; mobile diberi layout/fallback terencana, bukan sekadar scale down hingga teks tak terbaca.
- Semua elemen yang bergerak wajib punya asset/layer independen dengan anchor dan transform origin tepat: glass tube, socket, cradle, rotary knob, reset push-button, LED, display overlay, 6 slot, kartu tercetak, flap/roller dispenser.

### 1.2 Posisi awal lampu — KEPUTUSAN FINAL
- **Lampu awal diletakkan pada cradle/holder mekanis kecil di area DEPAN KIRI BAWAH mesin**, dekat socket lampu vertikal di paling kiri. Ini bukan di sisi kanan mesin. Desain cradle masuk akal sebagai bagian perangkat: dua penahan kuningan, lekukan untuk tabung, bantalan gelap, refleksi dan bayangan kontak.
- Lampu berbaring sedikit diagonal di cradle, tidak mengambang dan tidak terlihat sebagai dekorasi random; siluet tabung harus berhubungan jelas dengan socket kosong. Socket tetap terlihat di panel kiri.
- Saat power OFF, lampu boleh menerima **cahaya ambient eksternal** agar ditemukan pengguna, tetapi **tabung belum memancarkan cahaya sendiri**, LED mati, dan dua layar tidak backlit. Hindari visual bohong berupa indikator listrik menyala ketika tidak bertenaga.
- Bila layar kecil tidak punya ruang untuk cradle di samping, tempatkan cradle di area terdekat yang masih terlihat tanpa menutupi tombol atau konten. Jaga hubungan jarak visual dengan socket.

### 1.3 Redraw/cleanup asset seperti professional illustrator
- **Ganti dua kontrol bulat kiri yang terlihat kurang rapi**; jangan menambah efek animasi ke gambar tombol lama yang salah bentuk.
- **Kontrol ATAS = rotary knob**: metal knob beralur/knurled, volume realistis, marker arah kecil yang jelas, sumbu putar dan bayangan konsisten. Putaran berfungsi sebagai pagination dua arah.
- **Kontrol BAWAH = reset push-button**: badan/casing berbezel, muka tombol benar-benar bisa masuk/tertekan beberapa piksel, depression/contact shadow, rebound. **Jangan dibuat knob dan jangan dianimasikan berputar.**
- Gambar dan/atau SVG lampu berlapis (end caps, glass, filament/inner core, glare/reflection, glow terpisah), socket dan pengunci, penyangga cradle, print/dispenser. Gunakan visual asset lokal; jangan mengandalkan request asset gambar pihak ketiga saat runtime.
- Normalisasi arah cahaya, sudut perspektif, shadow/highlight, tingkat detail dan gaya rendering antara part lama dan part baru. Hindari garis kasar, stretched texture, bentuk geometris yang terlalu CSS-looking, clipping, blur tidak wajar, atau foto dengan perspective yang tidak cocok.
- Jika source asset tidak memungkinkan edit per-layer: rekonstruksi komponen interaktif sebagai local SVG/PNG/WebP berlapis dengan kualitas visual sepadan; hilangkan komponen lama dari base artwork secara bersih (mask/clean plate/rebuild). Jangan menyembunyikan mismatch visual dengan overlay buram.
- Gunakan resolusi memadai untuk DPR tinggi, kompres sesuai nilai visual, dimensi dan aspect-ratio eksplisit untuk mencegah CLS; jangan merusak original/source. Dokumentasikan asset source, file output dan transform-origin.

## 2. UX GAME DESIGN: DISCOVERY TANPA TUTORIAL PANJANG

Terapkan **environmental affordance -> invitation -> confirmation -> reward**:

1. **Initial state**: pengunjung melihat mesin tidak bertenaga; dua layar benar-benar off (kaca gelap plus refleksi ambient/siluet samar, bukan panel aktif), LED padam, tombol dan knob tidak berfungsi. Lampu terlihat resting pada cradle; socket kosong jelas mencocokkan bentuknya.
2. **Attention**: ambient specular highlight lembut pada lampu/cradle. Gunakan motion subtle dan jarang, bukan terus-menerus bergoyang. Pointer hover/focus pada lampu memperlihatkan reaksi penyangga, micro-lift/outline halus; `grab` cursor untuk mouse.
3. **Invitation**: sewaktu lampu mulai di-drag, socket merespons dengan ghost silhouette/guide, perubahan material highlight yang tidak menyala seperti listrik; pengguna memahami target tanpa panah besar atau teks `DRAG HERE` permanen.
4. **Confirmation**: ketika lampu mencapai area magnetik, socket memberi tanda implisit (outline/ghost aligned, ring metal bergerak sedikit, refleksi bertambah). Jangan attach saat cursor masih di-hold.
5. **Reward**: **hanya setelah pointer/touch dilepas di drop zone valid**, jalankan animated magnetic snap, lock, dan kemudian mesin boot on. Buat sequence terlihat memuaskan, terkontrol, dan tidak mengganggu akses konten.
6. **Contextual hint**: jika first-time user tidak berinteraksi selama sekitar 6–8 detik dan mesin masih off, munculkan tulisan kecil dekat cradle misalnya **“Lampunya belum terpasang.”**, tidak menghalangi machine. Hilang saat diinteraksi; tidak muncul berulang setelah berhasil. Jangan tampilkan pada reduced-motion/assistive flow jika mengganggu; informasikan status melalui accessible text.
7. **No gate**: seluruh navigasi website di luar permainan tetap tersedia. Publik tetap dapat membuka konten published melalui semantic links, termasuk apabila JS belum aktif, animasi gagal, atau pengguna tidak berminat bermain. Tidak ada tutorial wajib/modal blocking.

## 3. LAMP: DRAG, DROP, MAGNETIC SNAP, REVERSE OPERATION

### 3.1 Input behavior
- Gunakan Pointer Events untuk mouse/pen/touch, `setPointerCapture` untuk sesi drag yang stabil, pointer offsets yang menjaga posisi genggaman, state cleanup saat `pointerup`, `pointercancel`, kehilangan capture, Escape, unmount atau visibility interruption.
- **Saat pointer masih ditekan, lampu hanya mengikuti pergerakan tangan dan menampilkan preview**. Tidak boleh otomatis terpasang, tidak boleh mengaktifkan power, tidak boleh teleport ke socket.
- Hit-test terhadap visual anchor socket dengan koordinat yang benar di berbagai ukuran/transform/responsive. Buat **magnetic drop zone lebih besar daripada bentuk socket**, nilai awal 1,5–2x dimensi target atau berdasarkan jarak pusat yang bisa dikonfigurasi dan diuji; jaga agar tidak bertabrakan dengan slot/kontrol lain.
- Ketika pointer memasuki drop zone: ghost/alignment preview + accent highlight halus; ketika keluar, indikator hilang. Jangan terlalu agresif menarik lampu dari kursor saat sedang di-drag.
- **Pointer dilepas di zona socket** -> hitung target transform, animasikan translasi + rotasi + sedikit settle, **snap mengunci**, setelah animation/commit valid baru set `lampInstalled=true` dan boot. Saat gagal dilepas di luar zona -> lampu animasi kembali ke posisi ASAL (cradle untuk instalasi, socket untuk percobaan cabut yang gagal).
- **Bisa dicabut kembali**: drag dari socket ke drop zone cradle (cradle juga forgiving). Drop valid -> animasi lampu kembali berbaring di holder dan mesin melakukan power-down. Drop invalid -> kembali terkunci di socket; jangan diam-diam mematikan mesin ketika drag melewati area lain. Konsisten: state power berubah setelah drop valid/commit, bukan karena sekadar hovering.
- Hindari kehilangan posisi ketika halaman scroll, viewport resize, device rotate, atau navigasi halaman. Selama drag lock input yang relevan saja, jangan block scroll global secara permanen. Beri tombol/alternatif **tap lamp -> tap target socket** dan kebalikannya di perangkat touch, plus keyboard action menggunakan tombol berlabel jelas, agar tidak mewajibkan kemampuan drag presisi.
- Buat affordance screen reader (`aria-label` status dan aksi), live announcement singkat untuk connected/disconnected, visible focus rings; kontrol disabled punya status benar.

### 3.2 Motion tuning
- Animasi lifting ±100–160 ms; follow-pointer memakai `requestAnimationFrame` bila perlu, transform compositor-friendly, tanpa layout read/write thrashing.
- Zona magnetik memberikan feedback ±100–150 ms.
- Setelah release valid: snap ±200–320 ms dengan kurva ease-out/spring **berhenti stabil tanpa bounce kartun**; ada klik mekanis visual pada pengunci tepat di akhir.
- Boot: sekitar 700–1200 ms bertahap: lampu flicker 2–3 kali -> warm glow membias ke metal -> LED aktif berurutan -> koleksi kiri boot -> preview kanan hidup. Timing bukan hard requirement; tuning by eye dan ukur.
- Power-off: sequence lebih cepat; cahaya, LED, dan panel benar-benar padam. Sementara transition berlangsung, cegah repeated input / race. Prioritaskan transform dan opacity, glow tidak boleh memakan GPU berlebihan.
- Audio sound design boleh **opsional**, tidak autoplay dan bukan satu-satunya feedback. Fitur tetap lengkap tanpa audio.

## 4. POWER STATE MACHINE — SATU SUMBER KEBENARAN

Rancang state yang eksplisit, misalnya:

```js
{
  power: 'off' | 'booting' | 'on' | 'shutting_down',
  lampLocation: 'cradle' | 'socket' | 'dragging' | 'animating',
  lampOrigin: 'cradle' | 'socket' | null,
  pageIndex: 0,
  selectedSlotId: null,
  selectedVisitId: null,
  dispenser: 'idle' | 'printing' | 'ready' | 'retrieving',
  isInteracting: false,
}
```

Ini contoh struktur konsep, **adaptasikan dengan arsitektur existing**. Pisahkan source-of-truth dari DOM/render layer; jangan membuat beberapa boolean yang dapat menghasilkan kombinasi kontradiktif.

**Invariants**:
- Ketika OFF, layar tidak menyala, LED mati, knob/reset/slot/right-panel buttons/dispenser tidak memicu aksi. Elemen mekanis tetap terlihat dan lampu tetap dapat diinteraksi. Navigation links standar website di luar game tetap berfungsi.
- Hanya event release valid yang mengubah kepemilikan lampu; hanya transisi yang sah mengubah `power`.
- Saat `booting`, `shutting_down`, `printing` atau `retrieving`, jangan ada aksi duplikat, callback stale, uncontrolled re-entrancy, double print atau salah panel. Animasikan secara cancellable; cleanup saat page navigation/unmount.
- Untuk first session, default machine OFF. Simpan status successful power-on **per session** (contoh sessionStorage, dengan defensive fallback) supaya refresh/kembali ke homepage pada sesi yang sama tidak memaksa ulang. Ketika telah dipersist on, render langsung state on yang konsisten tanpa replay boot penuh atau flash gelap; bila lampu dicabut valid, persist OFF. Jangan gunakan DB untuk state permainan personal anonim.
- JS failure / unsupported input / reduced motion tidak boleh memutus akses konten publik.

## 5. KNOB KIRI ATAS: ROTARY PAGINATION DUA ARAH

- Bukan tombol power, bukan sekadar click next. Pengguna memutar knob melalui drag angular di sekitar pusat knob; mendukung searah & berlawanan jarum jam, dengan marker visual yang benar-benar mengikuti angle. Hitung delta angle secara kontinu tanpa jump saat melintasi -180/180 derajat (normalisasi sudut).
- Tetapkan **detent** yang masuk akal (mis. 25–40° per satu step, tuning setelah test). Satu notch -> satu halaman, tanpa jitter bolak-balik akibat noise pointer. Support controlled wheel hanya ketika jelas fokus/hover dan tidak mengganggu scroll; fallback klik sisi kiri/kanan, panah keyboard dan button accessible.
- `next` dan `previous` dapat melingkar dari akhir ke awal/awal ke akhir (infinite wrap) jika ada >1 page. Jika 0 atau 1 page, knob punya affordance idle yang sesuai, tidak memalsukan data baru.
- Isi panel koleksi menggunakan **data published yang sebenarnya**, chunk 6 per page (3×2 desktop) atau adaptasi mobile yang masuk akal. Jangan hardcode 18 item/demo atau mengulang item palsu agar halaman terlihat penuh. Jaga link/category/query filter sesuai domain Foodie.
- Perpindahan kartu terasa mekanis seperti cassette/reel: gerakan pendek, directional, stagger ringan, tidak membuang readability, tidak berkedip liar, dan tidak mengganti grid dengan blank yang panjang. Cegah race saat knob diputar cepat; boleh queue/skip ke final target dengan hasil deterministik.
- Plat `N° 01` dan indikator terkait mencerminkan state page dinamis (nomor halaman asli), dengan update accessible dan tidak mematahkan styling existing.

## 6. RESET BUTTON KIRI BAWAH: PHYSICAL PUSH

- **Reset adalah tombol tekan, bukan rotary dial.** Pada press: muka button bergerak masuk sedikit, contact shadow berubah, lalu spring rebound yang terkendali (±100–180 ms awal). Dukung pointer, keyboard Space/Enter.
- Saat power ON, reset mengembalikan halaman koleksi ke awal, pilihan/preview ke default yang valid, dan membersihkan atau menormalkan printed card state sesuai UX; tidak mematikan mesin, tidak menghapus data user/server.
- Saat power OFF, tidak ada efek reset, tidak ada active animation seolah sedang bekerja. Label aksesibilitas menjelaskan fungsinya.
- Visual reset/button casing harus dirender terpisah dari knob.

## 7. 6 SLOT, RIGHT DISPLAY, 3 INDICATOR BUTTONS

- Keenam kartu slot tetap menjadi area interaktif besar yang jelas. Setiap slot punya hover/focus/pressed/selected micro-state, dengan metal border dan glass depth; hindari layer decorative menutup pointer hit area.
- Menekan slot kategori/koleksi -> mengubah highlight + preview relevan di panel kanan tanpa menganggap item kategori otomatis sama dengan suatu kunjungan. Bila slot mengacu ke food/visit spesifik -> pilih konten spesifik itu. Seluruh link dan data publik wajib `published`, dengan server-side visibility enforcement.
- Panel kanan menampilkan konteks pilihan secara konsisten: foto, judul, tempat/kota, story excerpt, CTA kunjungan yang benar, fallback untuk gambar/metadata kosong. Jangan memakai label/salinan seolah Foodie menjual atau memasak makanan.
- Pertahankan 3 tombol LED kanan (`Pilih koleksi`, `Ambil memori`, `Baca cerita`) atau mapping semantik existing yang setara berdasarkan inspeksi. Tentukan aksi konsisten: pilih/konfirmasi slot terfokus; mencetak kartu memori dari **selected concrete visit**; buka story/detail visit published. Jika tombol tak punya target valid, beri disabled/feedback yang dapat dimengerti, jangan invent data.
- LED indikator visual memiliki dark inactive material saat off. Jangan menyamakan LED sebagai decoration yang selalu glow; gunakan amber accents konsisten dan accessible names/status.

## 8. DISPENSER: KARTU FOTO TERCETAK DAN BISA DIAMBIL

**Ini fitur wajib, bukan stretch goal.** Dibuat sebagai interaksi mini-game sungguhan.

- Saat pengguna telah memilih **visit/food yang merujuk ke satu kunjungan nyata**, sediakan aksi cetak langsung yang jelas. Untuk target spesifik, boleh auto-print setelah explicit selection dengan debounce dan status sehingga tidak tercetak berkali-kali; tombol `Ambil memori` juga dapat mencetak selected visit secara manual. Untuk hanya memilih kategori, jangan fabricate kartu visit tertentu: tampilkan preview/ajakan pilih kunjungan terlebih dahulu.
- Buat card/photo print asset dengan frame foto, label pendek, dan identitas visit yang benar (jangan teks acak); sumber gambar cover/thumbnail valid, fallback rapi ketika image unavailable. Card bergerak secara fisik dari balik slot, melewati paper flap/roller, kemudian berhenti setengah keluar.
- Tahap animasi: indikator mesin memproses -> kartu masuk jalur cetak/terdorong dari balik slot -> edge reveal dengan masking/clipping -> card settle pada pose accessible clickable. Target kasar 600–900 ms, dapat dituning. Tidak menggunakan full re-render/fade yang memalsukan proses cetak; posisi/sudut/occlusion realistis.
- Kartu yang siap dapat ditekan/click/keyboard Enter untuk membuka **route visit detail** yang sesuai (bukan dummy modal), atau ditarik keluar secara visual jika UX memungkinkan. Reset, pilihan baru, halaman baru, dan power-off harus memiliki aturan jelas untuk menyelesaikan/mengganti kartu saat ini; tidak ada overlapping card/zombie animation.
- Boleh ada efek fisik kecil saat kartu diambil dan opsi print ulang tanpa double execution. Saat mencetak cepat berulang, deduplicate/queue satu per satu dengan kebijakan jelas dan tested.
- Kalau kategori di slot tidak berkorelasi ke satu visit, manfaatkan kartu dari pilihan concrete visit di panel kanan atau hasil pilihan khusus, tidak memilih secara sembarang tanpa informasi ke pengguna.

## 9. RESPONSI, ACCESSIBILITY, MOTION, PERFORMANCE

- Desktop: proporsi besar dan rich lighting. Tablet: kontrol tetap reachable, area klik min 44×44 CSS px bila dimungkinkan. Mobile: reorganisasi panel atau tampilan bagian yang bisa digulir secara **sengaja**, semua komponen tetap terlihat dan terbaca; jangan memaksa gesture panjang dari sisi kanan ke kiri. Cradle dekat socket pada tiap breakpoint.
- Lampu: mouse drag, touch pointer drag dan fallback tap-to-select + tap-to-socket/cradle. Knob: angular drag dan fallback control. Dispenser/slot/buttons: keyboard, hover tidak menjadi satu-satunya feedback.
- Gunakan semantic button/link, `aria-label`, `aria-disabled` atau native disabled saat sesuai, live region singkat untuk status, focus management pada state changes, WCAG 2.2 AA target (teks >=4.5:1, UI significant >=3:1, visible focus).
- `prefers-reduced-motion: reduce`: hilangkan idle lure dan gerakan besar, gunakan transisi singkat/fade atau instant state, pertahankan perubahan yang informatif. Bila user mengaktifkan reduce motion saat runtime, jangan corrupt state.
- Optimasi transform/opacity, `will-change` hanya sewaktu animasi relevan, hindari banyak CSS blur/filter/box-shadow animasi besar, jangan membaca layout lalu menulis layout berulang kali tiap frame, jangan jadikan background raksasa berubah tiap pointer move.
- Tidak boleh membuat LCP/CLS/INP homepage memburuk signifikan. Lazy-load asset tidak kritis, preload seperlunya untuk first-screen, dimensikan image/video, asset WebP/AVIF jika sesuai dengan build. No unbounded event listeners, leak, or animation timers.
- Pastikan saat koneksi lambat atau tanpa foto: layout masih usable, skeleton hanya untuk loading data nyata, empty/error state kontekstual. Jangan menambahkan librari besar hanya untuk ease/spring.

## 10. ENGINEERING ARCHITECTURE

- Gunakan satu coordinator/state-machine layer untuk interaksi (`power`, `lamp`, `knob`, `display`, `dispenser`), tetapi pecah modul input/render menjadi file yang jelas dan reusable jika masuk akal. Jangan menaruh semuanya di satu Blade template berisi ribuan baris JS inline.
- Event lifecycle harus terkelola, animation promises harus di-cancel/diabaikan ketika stale, input handlers tidak boleh ganda bila partial/page re-initialize. Motion tokens/asset positions dikelola secara sentral lewat CSS custom properties/design tokens, bukan magic numbers berulang.
- Data: gunakan Eloquent query/scopes published existing, eager loading, pagination/chunk secara efisien, hanya published terindeks/terekspos publik. Jangan meminta API baru jika datanya bisa diberikan Blade secara ringan; jangan hardcode UI demo ke production.
- Bila perlu, tambah testable pure helpers (angle normalization, wrap index, drop zone geometry, transition guards). Pertahankan singular MySQL tables (`place`, `visit`, `visit_item`) dan konvensi Laravel project; tidak perlu migration hanya demi animasi.
- Gunakan sessionStorage dengan fallback defensif bila diperlukan; jangan melacak pengguna dan jangan membuat server write untuk interaksi anonim.
- Jangan ubah route, status content published/draft/private, SEO atau komponen shared tanpa alasan; jangan menghapus fitur sebelumnya agar mini-game bekerja.

## 11. VERIFICATION / ACCEPTANCE CRITERIA (WAJIB DIPERIKSA)

### Power dan lampu
- [ ] Fresh session: lampu di cradle depan kiri, socket kosong, layar kiri+kanan gelap, semua LED mati, semua kontrol mesin (kecuali lampu) tidak bereaksi.
- [ ] Lampu visually aligned with intended holder; asset tidak tumpang tindih maupun blurry pada desktop/tablet/mobile.
- [ ] Mouse hold & drag melewati socket **tidak** menyalakan mesin; indikator valid muncul saat masuk zona; menyalakan hanya ketika release valid.
- [ ] Release di sisi tepi/drop zone yang forgiving berhasil snap; release jauh mengembalikan lampu ke asal secara smooth.
- [ ] Drag lampu dari socket ke cradle lalu release valid mematikan mesin; gagal drop kembali ke socket dan mesin tetap on.
- [ ] `pointercancel`, mouse lost, page navigation, scroll/resize, double pointer, dan drag cepat tidak menghasilkan lampu hilang atau state rusak.
- [ ] Session persistence benar saat refresh/back tanpa flash glitch; lampu dan layar sesuai status tersimpan.

### Kontrol dan data
- [ ] Knob terlihat sebagai rotary knob realistis, berputar kanan-kiri via angular drag tanpa jumping, satu detent satu page, wrap benar dan no data duplication.
- [ ] Tombol bawah terlihat sebagai spring-loaded push-button, animasi **ditekan** bukan diputar; reset data/state pilihan, tidak power off.
- [ ] Knob/tombol/slot/LED kanan disabled sepenuhnya saat off; setelah on semuanya bekerja sesuai aksi yang terdokumentasi.
- [ ] Data slot dan preview benar-benar terhubung ke record published, dengan empty/last partial page yang wajar; harga dan filter (jika terlibat) tetap konsisten dengan domain.
- [ ] Menekan item/visit konkret dapat menghasilkan kartu yang dicetak; foto/label/tautan tepat; kategori tanpa visit spesifik tidak menghasilkan data fiktif.
- [ ] Kartu keluar dari dispenser melalui masking/occlusion yang wajar, clickable ke detail published; repeated clicks tak menghasilkan kartu rangkap/ghost.

### Visual, UX, a11y, regression
- [ ] Perbandingan visual dengan screenshot referensi: quality of metal, brass, glass, perspective, controls, gradients, illumination, bevel dan typography tetap padu; tidak ada asset yang tampak tempelan murahan.
- [ ] Interaction hints halus tanpa instruksi belibet; pengguna bisa menebak affordance; fallback tap/keyboard & accessible status berfungsi.
- [ ] Reduced-motion mode tetap fungsional; focus ring, contrast, semantic links valid.
- [ ] Tidak ada scroll horizontal liar, konten tertutup, accidental click ketika drag, layout shift besar, atau jank jelas pada desktop dan mobile.
- [ ] Navigasi standar Foodie dan visit/detail/explore tetap berfungsi tanpa memainkan mini-game, termasuk jika JS tidak berjalan.
- [ ] Jalankan unit/feature/browser tests yang relevan (existing runner); tambah test untuk helpers dan state transitions, serta screenshot/playwright jika telah tersedia. Laporkan perintah + hasil sebenarnya, bukan klaim.

## 12. DOCUMENTATION + DELIVERABLE FORMAT

1. Implementasikan kode lengkap, bukan hanya membuat instruksi baru atau menuliskan saran.
2. Perbarui dokumen arsitektur/frontend/design-system dan dokumentasi homepage interactivity yang tersedia (atau buat dokumen fokus jika tidak ada), mencakup: diagram state lampu/power/dispenser, mapping kontrol, asal dan cara edit layer asset, touch/keyboard fallback, durations/easing tokens, responsive behavior, session persistence, aksesibilitas, edge cases.
3. Jika dokumentasi lama bertentangan (mis. menyebut tombol atas sebagai power button / tombol bawah sebagai rotary dial), **koreksi** supaya tidak mengarahkan pengembangan berikutnya ke desain yang salah.
4. Siapkan inventaris perubahan berupa file berubah, asset baru/removed, data binding, dan test yang dijalankan. Ambil screenshot before/after desktop/mobile jika environment mendukung dan bandingkan nyata dengan acuan.
5. Berikan penjelasan singkat atas setiap deviation yang memang diperlukan karena kondisi repo/asset. Bila terdapat bagian yang tak bisa diselesaikan secara visual atau teknis, jelaskan dengan transparan bersama fallback, jangan silently mengganti konsep.

### Prioritas pengerjaan (jangan memangkas lingkup final)
1. Inspect repo + asset audit dan rekonstruksi layer mekanis.
2. Lampu/cradle/socket dan power-state yang testable.
3. Smooth drag/drop/magnetic release snap + boot/off sequences.
4. Rotary pagination + reset push-button + perbaikan 3 LED/control mapping.
5. Preview published records + card printing/dispenser.
6. Responsive/a11y/performance refinement, testing, screenshot comparison, docs.

**Keputusan desain final yang tidak boleh disimpangkan:** lampu awal di cradle depan kiri (bukan kanan); mesin mati hingga **drop valid setelah pointer dilepas**; indikator snap muncul sebelum release; knob atas berputar kiri-kanan untuk pagination; tombol bawah ditekan untuk reset; dispenser mencetak kartu foto kunjungan asli; semua animasi smooth, purposeful, cinematographic namun tidak membatasi akses konten.
