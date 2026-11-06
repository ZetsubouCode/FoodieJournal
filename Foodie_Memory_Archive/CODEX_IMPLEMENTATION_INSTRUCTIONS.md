# CODEX — FOODIE EXPLORE: MEMORY ARCHIVE (ART-DIRECTION LOCK)

> **Tujuan:** Implementasikan ulang page Explore Laravel Foodie sedekat mungkin dengan `reference_full_page.png`: dark cinematic culinary archive dengan mesin vending realistis, slot kategori berupa mini-still-life yang realistis, display kenangan menyatu, laci filter, Memory Reel fotografis, dan lighting copper/amber. **Bangun interaksi sungguhan**, bukan menempelkan satu screenshot sebagai seluruh UI. Paket aset lokal berisi fondasi mesin tanpa konten, overlay metal, background, tekstur, dan referensi.

## 0. Peran, prinsip, dan target

Berperan sebagai **Senior Creative Frontend Engineer, Technical Art Director, Motion Designer, Laravel Architect, Accessibility Engineer, dan QA**. Kerjakan dari audit hingga selesai, bukan berhenti pada rencana/mockup. Tingkatkan _visual fidelity_ melalui iterasi screenshot–perbandingan. Target desktop: kemiripan komposisi, warna, material, tipografi, pencahayaan, ritme ruang, dan kualitas fotografi yang setinggi mungkin terhadap referensi. Jangan menjanjikan pixel-perfect 1:1 pada teks/data yang dinamis dan viewport berbeda; fokus pada kesetaraan **art direction dan kualitas yang terukur secara visual**.

**Panduan fleksibilitas:** Angka, timing, dan struktur DOM di bawah adalah titik awal, bukan kewajiban geometris. Jika keadaan repo berbeda, pilih implementasi yang lebih baik, jelaskan alasannya, dan pertahankan kriteria hasil. Jangan menghindari kesulitan dengan mengubahnya menjadi 6 tombol, carousel, atau panel dashboard generik. Jangan menambah ilustrasi proyektor/wheel lama.

**Batas bisnis:** Website adalah jurnal pribadi, bukan toko makanan. Hanya konten `published` tampil di publik; tidak boleh mengarang kunjungan/ratings/harga/tag/counter. MySQL table names **singular** jika perlu migrasi, namun hindari migrasi baru bila preset config cukup. Default Explore adalah **Visit terbaru**, mode **Visit / Food / Place**, dengan filter URL persisten.

## 1. Persiapan / repo audit

1. Baca instruksi proyek dan dokumentasi terkait (sitemap, ERD, design system, Explore).
2. Inspeksi route/controller/query/model/Blade untuk Explore, Visit, Food, Place; navigasi, form pencarian, filter, sort, pagination, URL query, dan empty states.
3. Inspeksi `resources/css`, `resources/js`, Vite, Blade components, Alpine/Livewire, media rendering, serta tests. Reuse komponen alert, modal, toast, rating, dan filter yang sudah tersedia.
4. Inventaris file yang khusus dipakai Flavor Dial/projector lama. Setelah implementasi baru stabil, **hapus** aset dan kode lama yang tidak dipakai (dengan `rg`/references), tetapi jangan hapus aset yang dipakai halaman lain, user uploads, atau DB.
5. Catat screenshot baseline dan lakukan test baseline. Jika browser tooling ada, gunakan screenshot/video saat awal dan setelah perubahan.
6. Buka paket aset ini, baca `README_ASSETS.md`, dan inspeksi transparansi/dimensi tiap file; **jangan gunakan versi berisi teks sebagai satu bitmap interaktif**.

## 2. Sumber aset — fungsi yang tepat

Paket ini seharusnya berada di root repo sementara, misalnya `design_reference/foodie_memory_archive/`. Pindahkan hanya aset runtime yang diperlukan ke jalur final menurut konvensi repo, seperti `public/assets/explore/archive/`. Source/master dan referensi visual bisa disimpan di folder desain/dokumentasi, bukan dipaksa semuanya menjadi payload pelanggan.

| Aset | Fungsi | Boleh sebagai gambar runtime? |
|---|---|---|
| `reference_full_page.png` | **North-star** komposisi final | **Tidak**, referensi screenshot saja |
| `machine_filled_reference.png` | Referensi kualitas artwork mesin dan posisi panel | **Tidak** sebagai antarmuka dinamis (teks dan gambar baked-in) |
| `memory_machine_empty_cropped.png` | **Base realistis kosong** dari mesin, sudah menghilangkan foto/teks/isi layar untuk diganti dinamis | **Ya**; kandidat asset utama |
| `memory_machine_front_bezel_cropped.png` | Bezel metal dengan bukaan transparan untuk area slot/screen/tray | **Ya**; tumpuk di atas konten dinamis bila posisi sesuai |
| `memory_machine_empty_base.png` / `memory_machine_front_bezel.png` | Varian dengan kanvas original 1448×1086 | **Ya**, alternatif jika membutuhkan koordinat original |
| `machine_geometry.json` | Data koordinat awal slot/screen/pickup | Source reference; boleh dipakai dalam build/design tooling |
| `machine_geometry_overlay.svg` | Peta batas transparan untuk debugging | **Tidak** dalam runtime publik |
| `memory_envelope_extracted.png` | Referensi amplop terpisah; boleh diperbaiki/dibuat ulang jika edge kurang mulus | Opsional, setelah direview |
| `background_warm_foliage.png` | Background sinematik bertekstur + pencahayaan hangat | **Ya**, dengan positioning responsif |
| `texture_brushed_bronze.png` | Material overlay metal/console/card | **Ya**, secara hemat |
| `console_filled_reference.png` | Referensi material dan visual filter console | **Jangan** sebagai form statis; buat input HTML yang nyata |
| `memory_card_filled_reference.png` | Referensi look card dengan torn-paper edge | **Jangan** sebagai card statis; komponen data-driven |
| `machine_slot_content_mask_cropped.png` | Masker area 6 slot | Opsional untuk compositing |

**Catatan kualitas jujur:** Base kosong merupakan rekonstruksi _composited_ dari aset mesin berisi konten yang sebelumnya dihasilkan, **bukan** sumber layer orisinal dari generator. Material di dalam ruang slot belum setara detail fotografi aset referensi. Jika terlihat kurang realistis ketika diisi foto, **rekonstruksi ulang panel interior atau buat aset yang lebih cocok**; jangan mempertahankan cacat hanya karena file sudah tersedia. Pertahankan silhouette, brass framing, dan pencahayaan yang membuat referensi menarik.

## 3. Art direction: jangan kehilangan karakter ilustrasi

### Layout keseluruhan

Desain satu pengalaman vertikal terhubung, bukan mesin kecil di kiri + kotak showcase di kanan + toolbar form terpisah:

- **Atmospheric stage / introduction:** dark charcoal, text ivory + italic copper, lighting dari jendela/sidelight, lapisan foliage lembut. Intro cukup ringkas agar panggung mesin langsung terlihat.
- **Memory Archive machine:** kabinet horizontal realistis **mendominasi lebar hero**, bukan accessory kecil. Konsep utamanya `collection compartments → processing indicators → pickup → memory display`, semuanya **satu perangkat**.
- **Discovery Console:** terasa seperti panel metal/cabinet control drawer tepat di bawah mesin, memakai motif material sama tetapi form tetap native, terbaca, dan mudah difokus keyboard.
- **Memory Reel:** foto makanan asli dominan, list/grid card dengan texture editorial seirama, jangan lepas dari bahasa desain mesin, jangan jadi 4 kotak gelap tanpa foto.
- **Footer:** penutup elegan, tidak melampaui hero secara visual.

Gunakan whitespace terukur dan proximity: bagian yang saling berhubungan harus tampak berhubungan. Jangan membuat panggung terlalu tinggi pada laptop sehingga filter dan awal hasil terlalu jauh di bawah.

### Tampilan / material

- Dark metallic bronze, warm copper/brass rounded rims, aged patina ringan, shadow cavity yang menunjukkan kedalaman.
- Cahaya utama realistis: amber dari lampu kompartemen dan pickup, pantulan tipis di tepi logam, bounce halus di metal sekitarnya. **Jangan memberi glow seragam ke semua border.**
- Fotonya harus menggiurkan dan realistis. Slot perlu tampak seperti **display cabinet mini-still-life** (makanan/objek dipajang dengan internal spotlight), bukan icon flat di tengah tombol.
- Bokeh, grain, texture, dan ornament dipakai seperlunya. Tidak harus semua elemen glossy.
- Pilihan aktif jelas terlihat melalui kombinasi lampu, contrast, metallic rim, label, dan status — tidak hanya warna.

### Font

Audit font lama terlebih dahulu. Kandidat open-source yang mendekati mockup: **Cormorant Garamond** (headline, judul editorial, italic) dan **DM Sans/Manrope** (UI, metadata, form). Alternatif headline **EB Garamond** jika proporsi lebih dekat saat diuji. Jangan memaksakan font sebagai fakta identik dengan gambar AI. Gunakan subset/weights yang diperlukan, WOFF2 **self-host** bila sesuai, atur preload selektif, `font-display: swap`, typography scale responsif. Jangan menaruh teks pada raster mesin. Pastikan panjang kata bahasa Indonesia tidak tabrakan.

### Sistem warna

Gunakan existing semantic tokens; contoh _initial swatches_ (tune visually against artwork; jangan hardcode masif): `surface: #11100e`, `surface-raised: #241b15`, `ivory: #ede1d1`, `secondary: #b9a997`, `copper: #c98b53`, `brass-light: #f2bb77`, `outline: #785138`. Tetap WCAG AA pada teks/fokus. Glow metallic via composited light layer dengan intensitas rendah, bukan sekadar `box-shadow: 0 0 30px orange` di semua panel.

## 4. Cara teknis merakit mesin dari base kosong

**Prinsip:** dekorasi metal boleh raster; konten, input, hasil filter, status, foto, dan label **harus HTML/SVG yang dinamis dan accessible**.

1. Sediakan scene wrapper dengan aspect ratio yang cocok varian gambar; untuk desktop pertahankan `aspect-ratio` dari gambar. Gunakan `position: relative` + gambar mesin kosong dengan `width: 100%`.
2. Tempatkan overlay 6 kompartemen pada *relative coordinates* sesuai `machine_geometry.json`. Gunakan kontainer per slot, foto real published (representatif kategori) atau still-life yang semantik, label HTML, kode slot, detail highlight.
3. Letakkan screen dinamis di area display kanan, dengan foto, teks, count, dan CTA link yang benar. Buat state `ready`, `processing`, `dispensed`, `revealed`, `empty`, `error`.
4. Tambahkan bezel overlay **di atas** konten agar foto berada *di balik* bingkai metal. Pastikan gambar dasar, elemen slot, dan bezel memiliki ukuran, origin, dan alignment **sama persis**. Validasi `object-fit`, `aspect-ratio`, dan transform. Jangan meletakkan konten di luar aperture lalu menyembunyikan kesalahan dengan `overflow:hidden` global.
5. Jadikan tray dan amplop layer independen. Amplop harus keluar dari **di belakang lip tray**, bukan muncul di permukaan paling depan. `memory_envelope_extracted.png` bisa dipakai sebagai reference / prototype; bila edge kurang bersih, rebuild high-resolution SVG plus paper/photo texture.
6. Static lamp/glints dari aset boleh ditambah CSS/SVG layer untuk selected state. Hindari menggambar ulang seluruh logam dengan CSS di atas gambar, karena akan mengurangi realism-nya.
7. Gunakan source item dari DB. Jika foto tidak tersedia, gunakan fallback yang sengaja didesain dalam kompartemen kosong; **jangan gunakan makanan yang salah kategori**.
8. Boleh membuat modul komposisi sebagai satu Blade component dan state controller kecil; jangan duplicate filter query di frontend.

**Konflik geometri:** Source kosong ini disiapkan sebagai **base untuk dikembangkan**. Jika crop koordinat tidak cukup presisi, koreksi secara terpusat dan jelaskan. Pakai capture pada ukuran aslinya untuk mengecek kebocoran teks baked-in, jagged edge, dan layering. Bila perlu buat ulang aset yang lebih akurat dari referensi dengan alat lokal/plugin yang sudah ada, tetapi tetap jaga engine ringan dan mandiri di runtime.

## 5. Kurasi slot dan data: enam entry point, bukan seluruh taksonomi

Mesin berisi **6 curated discovery presets**, bukan seluruh kategori/tag Foodie. Isi nyata berdasarkan repository; contoh A1 Kopi, A2 Pastry, B1 Mi & ramen, B2 Bandung, C1 Favorit pribadi, C2 Surprise Me. Label contoh ini bukan kewajiban literal, dan jangan pasang yang tidak punya relasi valid. Pastikan seluruh slot punya nama/tipe yang jelas dan fallback jika preset tidak memiliki hasil.

- Data preset cukup via config PHP / existing DB metadata bila tidak perlu CRUD admin.
- Preset dapat berisi filter jenis/kota/rekomendasi atau gabungan kriteria.
- Mode `Visit / Food / Place` tetap ada dan default **Visit terbaru**.
- Slot mengubah filter dalam URL dengan sistem query Explore existing, bukan membuat query engine kedua.
- Filter lanjut tetap lengkap: kota, kategori hierarkis/jenis, cooking style multi-tag, tag umum, label rekomendasi, rating, harga item vs total visit, sort, grid/list.
- Gunakan searchable dropdown untuk daftar panjang; preserve parameters yang relevan di URL ketika ganti mode.
- Hanya `published` muncul di pilihan, preview, count, results, SEO/API publik; enforce di backend.
- Saat user klik `Surprise Me`, pilih dari published yang valid. Jaga empty state jika nol hasil.

## 6. Koreografi animasi yang natural, tidak offside

**Ini kriteria utama.** Buat **motion storyboard** sebelum timeline animation. Dalam dokumentasi, untuk setiap elemen bergerak tulis: start, finish, parent coordinate, clip boundary, occlusion order, timing owner, interrupt strategy.

**A — Idle**: machine art loaded, 6 slot terlihat, status inviting. Hanya sedikit ambient lamp; no distracting loops. Default results sudah terlihat tanpa harus bermain mesin.

**B — Hover/focus**: warm lift on the chosen glass compartment (bukan seluruh kabinet), label dan kode terbaca, fokus keyboard setara hover.

**C — Select**: active rim/lamp meningkat intensitas, status menunjukkan pilihan; pemilihan filter/URL/update hasil terjadi dengan cepat dan tidak bergantung pada berakhirnya animasi dekoratif.

**D — Processing**: lampu indikator yang relevan bergerak secara kausal menuju pickup, progress singkat; tidak perlu fake load 2-3 detik. Biarkan waktu asli request mengendalikan loading.

**E — Dispense**: amplop mulai **dari dalam tray**, bagian bawahnya tertutup front lip, bergerak keluar sesuai arah opening. `transform-origin`, `z-index`, `mask`, `overflow`, dan posisi berbeda viewport harus diverifikasi **di setiap frame**, bukan hanya awal/akhir. Jangan pernah ada teleport, keluar tembus besi, tersangkut di bounding box, atau clip shadow secara tidak sengaja.

**F — Open and reveal**: amplop bisa diklik untuk membuka foto, tetapi membuka amplop **tidak wajib** untuk melihat hasil Explore. Foto dapat muncul dengan reveal pada integrated screen; crossfade berbasis satu sumber data. Jika hero photo bergerak antar layer via FLIP, uji semua viewport. Jika FLIP terlalu fragile, pakai reveal yang lebih sederhana namun meyakinkan.

**G — Memory Reel**: ikut filter dengan transisi halus, skeleton hanya untuk request nyata, scroll jump tidak boleh mendadak. User boleh langsung search/browse tanpa menunggu animasi mesin.

**Repeated use**: versi pertama boleh dramatis tapi ringkas; penggunaan berikutnya lebih cepat. Di tengah animasi kalau input baru, cancel/re-target dengan deterministik. Cegah double-submit, stale async response, dan status yang tidak pernah selesai. Gunakan `prefers-reduced-motion` untuk memotong perjalanan fisik.

**Motion choices**: GSAP core (`npm install gsap`) **opsional** jika tidak tersedia engine cocok di repo dan koordinasi timeline benar-benar terbantu. GSAP resmi dapat dipasang dari npm dan menyediakan timeline; jangan tambah GSAP sekaligus library animasi kedua/Three.js tanpa kebutuhan nyata. Alternatif WAAPI/CSS/Alpine memadai untuk rangkaian pendek. Jangan install plugin UI berat hanya untuk efek.

## 7. Discovery Console — fungsi nyata berbalut material yang sama

Bukan menggunakan `console_filled_reference.png` sebagai background yang berisi tulisan bawaan. Jadikan itu **referensi visual material dan proporsi**; buat form HTML semantik yang mengikuti struktur existing dan cocok dengan metal surface.

- Tab Visit/Food/Place berfungsi dan state persisten.
- Search text, filter count, sorting, grid/list, active chips, clear filters.
- Filter lanjut melalui modal/dialog atau sheet existing dengan search & multi-select yang tepat; focus trap/restore, Escape, scroll lock, ARIA.
- Pada desktop console dapat terlihat seperti panel cabinet yang berhubungan dengan mesin; pada mobile jadi kontrol accessible dan ringkas.
- Jangan menyembunyikan search/filter dasar di balik interaksi vending machine.

## 8. Memory Reel: foto wajib terlihat

Buat kartu domain data-driven dengan foto representatif, editorial type, rating/rekomendasi yang sesuai, place/date/city, tags yang valid. Foto **tidak boleh blank** seperti screenshot Explore terbaru. Pastikan aspect ratio, crop dan file path lewat image pipeline yang valid; sediakan placeholder sengaja didesain jika benar-benar tak ada foto.

Gunakan material aged-metal/paper tear secukupnya; prioritas pada foto nyata. Jangan pakai `memory_card_filled_reference.png` sebagai card berisi data karena baked dan palsu. Gunakan image itu untuk border/material/typographic guidance.

## 9. Asset optimization dan backgrounds

- `background_warm_foliage.png` adalah full composition wallpaper dengan warm-window light; pakai positioning dan gradient overlay yang tidak menghilangkan intensitasnya, tapi tidak mengorbankan kontras.
- `texture_brushed_bronze.png` dapat dipakai dengan CSS overlay via pseudo-element; selektif agar tidak mengulang pattern yang kelihatan.
- Gunakan WebP/AVIF untuk raster besar jika pipeline mendukung; simpan source PNG master di folder desain, hindari kompresi source destruktif.
- Lazy-load aset Memory Reel di bawah fold; preload critical machine/hero secara terukur. Optimize LCP/CLS/INP.
- Jangan menyematkan asset base64 raksasa di Blade/CSS; gunakan Vite/Laravel Filesystem sesuai struktur.
- Jaga semantics design tokens dan readable AA contrast; tidak semua teks harus copper.

## 10. Responsive composition

- **Desktop (mis. 1440×900):** mesin horizontal mendominasi hero seperti referensi, screen dan slots menyatu; controls dan beginning of Memory Reel mudah ditemukan.
- **Laptop 1280×800:** tidak boleh ada tabrakan heading, mesin, lampu; font dan spacing disesuaikan.
- **Tablet:** boleh reflow dua area menjadi vertical, tetap menjaga hubungan visual dalam satu staging scene.
- **Mobile 390×844:** jangan shrink bitmap mesin beserta teks sehingga tombol tak terbaca. Boleh pakai compact re-layout: slot controls sebagai mini-comps, screen di bawah, dispensers diringkas; pertahankan signature interaction dan material language. Jangan ada horizontal overflow.
- Semua tap targets operable, no hover-only info.

## 11. QA dan dokumentasi wajib

### Static visual QA

Dengan browser automation, ambil screenshot desktop/laptop/mobile dan **bandingkan side-by-side** dengan `reference_full_page.png`. Evaluasi: silhouette mesin, porsi hero, foto makanan visible, background lighting, metal highlights, typography hierarchy, console relationship, cards, footer. Minta hasil visual meaningful, bukan checkbox kosong. Jika rasa art direction masih jauh, lakukan iterasi.

### Motion QA

Rekam atau capture state: idle, hover, select, processing, envelope 25% keluar, 50%, 100%, open, screen revealed, results updated, repeat switch, rapid clicks, reduced-motion, empty/error. Pastikan tidak ada layering/clipping/offscreen/flash/snap yang salah. Bila ada bug animasi, perbaiki sumber struktur/timeline, jangan masking global.

### Function/security regression

Jalankan existing tests + tambahkan case yang relevan: `published` only, valid/invalid preset, empty result, URL filter preservation, browser back/forward, modes, count consistency, sorting, reset, search, no missing images, payment/auth tidak terpengaruh. Periksa console JS dan network 404. Jangan mengaku test lulus bila belum dijalankan.

### Documentation

Update docs agar menggantikan keputusan Flavor Dial/projector yang telah ditinggalkan, dokumentasikan visual system, asset usage, dynamic layer bounds, state model, modal/filter, responsive behavior, and animation test recipe. Jangan menyimpan code dan assets lama yang orphan.

## 12. Checklist acceptance dan deliverables

- [ ] Semua asset yang diperlukan tersimpan dan optimal, user data tidak terganggu.
- [ ] Desain Explore jelas mengambil `reference_full_page.png` sebagai art direction target.
- [ ] Gambar mesin kosong dipakai sebagai visual **shell**, teks/foto/slot UI adalah layer dinamis.
- [ ] Mesin dan screen tampak satu perangkat realistis, bukan dua panel.
- [ ] Proper compartment glass, still-life lighting, metallic edge reflections.
- [ ] Alur pemilihan → dispense → reveal terlihat natural dan tidak offside.
- [ ] User tidak wajib memainkan mesin untuk browse, filter dan results.
- [ ] Discovery Console & Memory Reel tersambung visual dan fungsional.
- [ ] Tidak ada blank photo, mismatch count/category, hardcoded fake review.
- [ ] Responsive/keyboard/reduced-motion/empty/error berfungsi.
- [ ] Aset & kode Flavor Dial/projector usang dibersihkan aman.
- [ ] Screenshot, video jika tersedia, dan hasil test nyata tersedia.
- [ ] Docs, README aset, dan laporan perubahan diperbarui.

**Selesaikan implementasi penuh.** Bila ada limit tool yang menyebabkan aset tidak bisa dibuat atau visual tidak bisa diverifikasi, jelaskan secara eksplisit dan kerjakan solusi terbaik yang tersedia; jangan klaim hasil final 1:1 tanpa evidence.
