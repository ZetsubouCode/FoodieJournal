# FOODIE — Design System Specification

> **Versi:** v1.1 + kontrak experience 8 November 2026 · **Status:** Spesifikasi terpelihara; domain tetap. [Status implementasi](docs/implementation-status.md) memisahkan baseline teruji dari target pending.
> **Acuan:** `product-blueprint.md`, `sitemap.md`, `erd-database-specification.md`, `user-flow-wireframe.md`  
> **Prinsip:** **Editorial, bukan template AI generik**; visual khas, komponen konsisten, operasional mudah, aksesibel, cepat.

## 1. Design direction

**Karakter**: dark cinematic editorial storytelling × bold experimental discovery. Foto menjadi narasi utama. Background charcoal, typographic contrast warm ivory, aksen amber/copper yang digunakan terukur. Tidak ada *random gradient*, neon, blur atau shadow berlebih demi terlihat “premium”. Komposisi asimetris didorong pada scene editorial bila hierarki baca, navigasi, aksesibilitas, dan mobile tetap jelas.

### Sistem komposisi visual (acuan kanonis)

**Kekayaan visual yang disengaja, interaksi yang sederhana.** Whitespace memberi ruang baca, penekanan, keseimbangan foto, atau arah pandang; area kosong besar harus punya tujuan komposisi. Padding besar saja bukan pengganti keputusan desain. Sebaliknya, jangan menambah ornamen untuk mengisi setiap celah.

- **Tipografi editorial:** display serif dapat menjadi judul ekspresif, angka status besar, indeks section, atau penanda cerita. Sans-serif mendukung isi dan kontrol; metadata ringkas boleh memakai tracking yang berbeda. Variasikan skala dan susunan judul sesuai konteks, dengan batas reflow dan keterbacaan.
- **Lapisan terarah:** teks di depan foto, kolom asimetris, offset, stroke, tekstur halus, rule tipis, atau anotasi boleh dipakai bila memperjelas narasi, identitas, atau hierarki. Tidak ada kewajiban memakai semua unsur pada setiap halaman.
- **Pola komposisi:** split editorial hero, full-bleed photo, image-led story, asymmetric grid, text-led section, layered feature, compact information layout, dan immersive detail adalah pilihan layout. Jadikan CSS primitive bersama hanya setelah dipakai berulang secara nyata; bukan komponen Blade wajib.
- **Ritme halaman:** selingi bagian padat/lapang, foto/teks, skala judul besar/kecil, dan wilayah lebar/sempit sesuai alur konten. Konsistensi datang dari token, tipografi, jarak, dan perilaku interaksi; variasi tidak boleh acak.
- **Kekayaan vs noise:** foto yang punya subjek, angka yang bermakna, layering terukur, dan kontras hierarki adalah kekayaan. Badge palsu, kartu repetitif, border/gradien/glow berlebih, copy filler, dan beberapa jangkar visual yang berebut perhatian adalah noise.

**Kepadatan menurut permukaan:** halaman publik mengutamakan foto kuliner, penemuan, dan narasi editorial; admin mengutamakan input cepat dan informasi padat yang terstruktur; halaman utilitas tetap beridentitas Foodie dengan pesan dan pemulihan yang segera jelas. Primitives (button/input/alert/modal/focus) dan domain components (visit/food/place card, gallery, rating) memerlukan konsistensi. Komposisi scene memerlukan fleksibilitas terkontrol; satu bentuk kartu tidak boleh menentukan semua halaman.

**P0-H, pilihan pemilik 9 November:** shell visual publik adaptif hingga 152rem, gutter responsif bersama, prosa 68ch; lebar kabinet native 1634×963 tetap. Area visual boleh lebar/asimetris, tidak melebarkan semua paragraf/foto tanpa alasan. Place memilih cover published yang benar-benar tersedia secara stabil lintas halaman histori; tanpa foto gunakan identitas/fakta asli dan komposisi material, tanpa kolom foto kosong. Admin choice 4A memakai **system UI font untuk isi/judul**, latar semantik polos, hierarki form/list praktis, shell 80rem dan footer ringkas. Ini menggantikan pewarisan font/atmosfer vintage publik pada admin. Choice 6A: skeleton real-wait baru milik **P2**; P0-H hanya audit fondasi dan verifikasi busy/empty/error/retry yang jujur. [Keputusan lengkap](docs/adr/002-p0-hardening.md), [bukti saat ini](docs/qa/p0-hardening/verification.md).

**Periksa sebelum ship:** jangan gunakan centered message untuk semua kebutuhan, tiga kolom seragam tanpa alasan konten, hero kosong tanpa tujuan, atau penumpukan rounded card. Centered layout, kartu, grid, dan gradien boleh bila konteks dan fungsi membenarkannya. Bandingkan 320/375/390/768/1366/1920, viewport pendek dan landscape, 200% zoom, keyboard, reduced motion, serta foto dengan fallback.

**HTTP errors — Editorial Bold:** `403/404/419/429/500/503` memakai satu scene: teks di kiri pada desktop, pot dan uap di kanan, angka status HTML serif besar dengan stroke tembaga dekoratif, lalu judul/pesan/tindakan berbeda. Mobile memakai crop foto di atas dan isi pada bidang gelap di bawah. Sumber foto: `docs/references/cinematic-kitchen-source.png`; turunan web: `public/assets/images/error/cinematic-kitchen.webp`. Styling kritis mandiri di `public/assets/css/error.css`, digenerasi dari `resources/css/tokens.css` dan `resources/css/error.css` oleh `npm run build`. Tanpa foto, teks harus tetap terbaca. Jangan masukkan kode/teks ke gambar; jangan tampilkan data exception; retry hanya untuk GET/HEAD. Uji semua status, JSON, mode maintenance Laravel tanpa `--render`, kontras, fokus, reflow, dan crop nyata. Referensi implementasi: `resources/views/errors/layout.blade.php` dan `config/error-pages.php`.

### Brand Voice & Microcopy (acuan kanonis)

Foodie adalah **jurnal kuliner personal milik satu penulis**. Penulis mengunjungi tempat makan dan mencatat pengalaman, hidangan yang dicoba, foto, harga, rating, serta rekomendasi pribadi. Foodie tidak memasak, menyajikan, menjual, atau mengantar makanan; bukan restoran, layanan pemesanan, komunitas ulasan, atau otoritas rating objektif.

- **Nada:** personal, alami, dekat, jelas, dan ringkas. Ekspresi editorial boleh muncul saat membantu cerita pengalaman; pesan utilitas harus mendahulukan kejelasan. Gunakan bahasa Indonesia yang terasa ditulis manusia, tanpa metafora paksa atau copy promosi generik.
- **Istilah:** gunakan *catatan*, *kunjungan*, *pengalaman*, *hidangan yang dicoba*, *tempat*, *pilihan pribadi*, dan *opini penulis* sesuai konteks. Rating adalah opini satu penulis, bukan penilaian komunitas atau jaminan objektif. Jangan menyebut sajian atau dapur Foodie seolah Foodie mengoperasikan tempat makan.
- **Flavor text:** pertahankan hanya bila memberi konteks atau karakter yang relevan. Hapus kalimat dekoratif yang menunda informasi atau tidak membantu pembaca. Fotografi pot, uap, dan makanan boleh membangun atmosfer visual; **teks tidak boleh mengubah atmosfer itu menjadi klaim bahwa Foodie memasak atau melayani pelanggan**.
- **Error:** judul menyatakan keadaan, deskripsi singkat menjelaskan dampak atau langkah berikutnya, dan label tindakan harus sesuai tujuan sebenarnya. Hindari menyalahkan pengguna, membocorkan detail internal, dan mengarang penyebab gangguan. Enam judul/deskripsi HTTP yang disetujui adalah sumber tunggal di `config/error-pages.php`.
- **Empty state:** nyatakan apa yang belum tersedia dan satu langkah yang masuk akal; jangan membuat cerita, ulasan, atau konten palsu. **Notifikasi:** sebut perubahan yang benar-benar terjadi dan tindakan bila gagal, tanpa perayaan untuk penyimpanan rutin. **Admin feedback:** jelaskan objek dan status simpan/publikasi, pertahankan isian saat gagal, serta beri alasan validasi yang dapat diperbaiki.

**Anti-AI-slop acceptance bar:**

- Jangan memisahkan setiap section dengan `border-bottom`/`<hr>`/garis horizontal panjang. Gunakan **white/negative space**, hierarki tipografi, perubahan layout/alignment yang masuk akal, section background jika benar-benar perlu.
- Hindari copy filler: tidak ada kalimat promosi yang tidak membantu pembaca menemukan makanan, memahami pengalaman, atau menilai konteks data. Label seperti “Crafted with passion…” dilarang tanpa alasan produk.
- Jangan tampilkan card 100% seragam seperti katalog e-commerce jika editorial narrative bisa lebih baik. Kombinasi satu featured large dan card biasa boleh tetapi punya **aturan responsive**, bukan posisi acak.
- Jangan mendesain tombol sekelas dengan ukuran berbeda karena panjang caption. **Satu action group** menetapkan tinggi, min-width, padding, dan flex behavior yang sama; perbedaan visual datang dari variant/priority, bukan teks panjangnya.
- Gradien hanya bila punya fungsi nyata (contoh overlay foto untuk memastikan kontras teks); gunakan linear overlay minimal, tanpa gradasi aksen dekoratif.
- Tidak ada dekorasi palsu, badge yang tidak bermakna, ikon repetitif, animasi hanya karena halaman tampak kosong, atau CTA ganda menuju tujuan sama.

## 2. Token semantic (usulan awal, wajib uji kontras)

Pisahkan raw palette dari semantic tokens; **nilai ini proposal**, target WCAG tetap divalidasi dengan alat kontras dan UI aktual sebelum dikunci.

```css
:root {
  /* Primitive palette, jangan dipakai langsung pada komponen domain */
  --palette-charcoal-950: #111211;
  --palette-charcoal-900: #191b19;
  --palette-charcoal-800: #242724;
  --palette-ivory-100: #f5f0e7;
  --palette-ivory-300: #d5cec2;
  --palette-copper-400: #e2a76b;
  --palette-copper-500: #c8894e;
  --palette-red-300: #ff9b92;
  --palette-green-300: #9fd7aa;
  --palette-blue-300: #9ec9e3;
  --palette-yellow-300: #edcb88;

  --surface-page: var(--palette-charcoal-950);
  --surface-raised: var(--palette-charcoal-900);
  --surface-hover: var(--palette-charcoal-800);
  --text-primary: var(--palette-ivory-100);
  --text-secondary: var(--palette-ivory-300);
  --text-muted: #b3ada3; /* harus diuji terhadap surface yang dipakai */
  --border-subtle: #41443f;
  --action-primary-bg: var(--palette-copper-400);
  --action-primary-text: var(--palette-charcoal-950);
  --action-secondary-bg: transparent;
  --action-secondary-text: var(--palette-ivory-100);
  --action-destructive-text: var(--palette-red-300);
  --status-info: var(--palette-blue-300);
  --status-success: var(--palette-green-300);
  --status-warning: var(--palette-yellow-300);
  --status-error: var(--palette-red-300);
  --focus-ring: #f2c788;

  --space-1: .25rem;
  --space-2: .5rem;
  --space-3: .75rem;
  --space-4: 1rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-12: 3rem;
  --space-16: 4rem;
  --radius-sm: .375rem;
  --radius-md: .75rem;
  --radius-lg: 1.25rem;
  --shadow-popover: 0 12px 36px rgb(0 0 0 / .24);
  --control-height-sm: 2.25rem;
  --control-height-md: 2.75rem;
  --control-height-lg: 3rem;
  --action-min-inline: 7.5rem;
  --content-max: 80rem;
  --motion-fast: 140ms;
  --motion-standard: 220ms;
  --z-header: 20;
  --z-dropdown: 40;
  --z-modal: 80;
  --z-toast: 100;
}
```

Gunakan CSS custom properties semantik dengan `var(--...)`; classes utilitas boleh untuk layout namun tidak menyebarkan nilai hex. Font display editorial dan font body yang readable memakai font sistem atau font gratis yang di-host sendiri sesuai lisensi; cek lisensi dan performa sebelum memutuskan keluarga font. Fallback tetap nyaman. Scale type dengan `clamp()` dan batas minimum jelas, jangan hero text memotong di 320 px / zoom 200%.

**Accessibility minimum:** teks normal ≥4.5:1, teks besar dan komponen penting ≥3:1; outline fokus kontras, keyboard only, link discernible; WCAG 2.2 AA target. WCAG 2.2 SC 2.5.8 meminta target **≥24×24 CSS px** atau jarak pengecualian yang valid; standar internal **≥44×44** untuk tombol touch utama guna lebih nyaman (ini rekomendasi desain, **bukan** angka mandatory WCAG AA). Reduced motion dan responsive text reflow wajib.

## 3. Layout, spacing, dan responsive

- **Grid dasar**: 4–6 px base spacing? **Tidak**; gunakan skala token 4 px untuk komponen dan section rhythm 24/32/48/64/96 px sesuai breakpoints. Hindari menaruh divider setiap 24 px; lebih baik kelompok dengan gap.
- Mobile `320–767px`: satu kolom utama, header ringkas, filter sebagai sheet/dialog, bottom sticky actions hanya bila tidak menutup konten, off-screen photos lazy. Min touch area 44 px.
- Tablet `768–1023px`: 2 kolom konten bila masih readable, filter bar terlipat / sidebar optional.
- Desktop `>=1024px`: shell publik fluid/lebar sesuai [art-direction](docs/art-direction.md#wide-responsive-shell), dengan prose 60–75ch. Batas 1280px adalah baseline implementasi lama, bukan target permanen. Explore tetap memakai drawer terarah; tidak wajib menambah sidebar.
- **Breakpoints bukan semata ukuran device**: ubah hanya ketika konten membutuhkan; uji 320, 375, 768, 1024, 1440, 200% zoom dan landscape.
- Surface layering halus. Hairline divider dipakai **seperlunya** untuk baris tabel, struktur form kompleks atau batas dialog, bukan dekorasi default.

## 4. Komponen dan penggunaan

| Primitive / domain component | API/intensi | Aturan quality |
|---|---|---|
| `Button` | variant `primary/secondary/tertiary/destructive`, size `sm/md/lg`, loading, disabled, icon | Semua tombol dalam **satu action group** tinggi/min-width setara, loading tidak mengubah ukuran, teks panjang wrap hanya jika layout memang mendukung; jangan hardcode width per label. |
| `Input`, `Textarea`, `Checkbox`, `Radio`, `Select` | label, help text, error, required | Label permanen; placeholder bukan label; server errors dekat field; disabled terbaca dan bisa dijelaskan. |
| `Combobox/Autocomplete` | `single/multi`, filter, clear, create-new optional | Gunakan untuk place, food_type, cuisine, style, ingredient, taxonomy panjang. Keyboard arrow/Enter/Escape/Tab, ARIA WAI APG; loading/no-results, debounce, hasil duplicate warnings. |
| `Chip/Badge` | selected filter, status, taxonomy | Badge tidak clickable jika bukan action; filter chip memiliki remove affordance + accessible name. |
| `Tabs`, `SegmentedControl`, `ViewToggle` | Visit/Food/Place, grid/list | Preserve URL, fokus visual; selected state tak hanya melalui warna. |
| `PriceDisplay`, `RatingDisplay` | label semantik eksplisit | Food unit price != Visit total; null → “Belum dicatat”; rating null → “Belum Dinilai”. |
| `VisitCard/FoodCard/PlaceCard` | cover, metadata, primary action | Card clickable tanpa CTA redundan; jangan nest anchor di dalam anchor; stable aspect ratio, alt dan fallback. |

### Visit Card — F Refined / Split Narrative (approved)

Referensi visual: `docs/design/references/visit-card-f-refined.png`. Satu komponen Blade `resources/views/components/domain/visit-card.blade.php` dipakai oleh Explore Visit, detail Place, dan kunjungan terkait. Home memakai `components/home/editorial-entry.blade.php` untuk recent/picks; pengecualian komposisi ini tidak mengganti identitas F Refined. Pada kartu lebar, foto memakai 39% area grid (tepi terlihat sekitar 34–38% setelah sobekan) dan panel charcoal sisanya. Grid Visit umum paling banyak dua kolom dan satu di bawah 1180px; Explore memakai aturan padat sendiri, satu di bawah 960px dan dua hingga 1999px. P0-H menambah tiga kolom Explore Visit / empat Food/Place pada ≥2000px; daftar tetap satu kolom. Pada lebar viewport di bawah 700px kartu umum menaruh foto di atas panel. Judul serif, eyebrow "Catatan kunjungan", tempat, rating, rekomendasi, tanggal/kota, lalu tag mempertahankan urutan referensi. Tidak ada tombol panah atau CTA duplikat.

Kontur tepi luar, top/bottom/right, dan highlight tepi adalah `public/assets/visit-card/{outer-mask,outer-edge,mobile-outer-mask,mobile-outer-edge}.svg`. Foto dipotong oleh `photo-mask.svg` atau `mobile-photo-mask.svg`; serat/sliver krem dan bayangan memakai `vertical-tear.svg` atau `horizontal-tear.svg`. `panel-grain.svg` memberi tekstur halus tanpa foto screenshot. Geometri tepi foto dan strip serat dibangun dari titik kontur yang sama oleh `tools/generate-visit-card-edge.py`, yang mencatat koordinat hasil penelusuran referensi 556×360; aset yang dihasilkan bersifat statis, lokal, dan dapat dipakai ulang. SVG memakai `preserveAspectRatio="none"` agar mengikuti tinggi konten dan tidak membuat seam berulang. Token `--visit-card-*` di `resources/css/tokens.css` mengatur warna kertas, bintang, chip, dan fallback foto. Kualitas sumber membatasi presisi serat mikroskopis, sehingga ini rekonstruksi kontur stabil, bukan salinan piksel.

`<article>` berisi satu tautan overlay Visit Detail dengan nama aksesibel dan tautan rekomendasi/tag sebagai saudara terpisah. Panel melewatkan pointer ke overlay, sedangkan kontrol internal berada di atasnya; fokus memakai ring tembaga yang terlihat. Rating memakai perhitungan overall proyek dan menampilkan "Belum Dinilai" saat null; rekomendasi tetap independen dari rating. Tag berasal dari data pada visit published: kategori/jenis hidangan, lalu teknik memasak, lalu tag umum, tanpa nama duplikat. Dua tag awal tampil; sisanya di tombol `+N` yang membuka popover dengan tautan Explore sesuai parameter kategori/food_type/style/tag. Escape mengembalikan fokus, click-away dan keluar fokus menutupnya. Tidak ada baris tag saat kosong. Foto menggunakan variant `card` dengan `srcset`/`sizes`, lazy loading, dan fallback saat variant tidak ada atau gagal dimuat. Zoom foto dan highlight tepi hanya 220ms pada hover/fokus, dan berhenti dengan reduced motion.
| `PhotoGallery/Viewer` | thumbnails optimized, modal original | Viewer close/escape/keyboard/zoom/touch, loading and failure, original protected route; credits/alt jika ada. |
| `CustomAlert` | severity, title, description, optional action/dismiss | Inline persisten untuk hal penting, bukan toast-only. |
| `CustomModal/Dialog` | sizes, named slots, destructive variant | Focus trap/restore, Escape, scroll lock, accessible description, confirmation explicit. |
| `ToastService` | info/success/warning/error, stack, timeout/pause | Aria live announcement, dismiss, batasi stacking, tidak untuk error yang memerlukan koreksi. |
| `Skeleton/Empty/Error State` | per domain + state nyata | Skeleton hanya saat loading nyata dan mencegah CLS; empty state menjelaskan konteks dan aksi. |
| `AdminFieldGroup`, `AdminStep`, `FormActions` | group short input + progress | Auto default, inline create, focus first error, save draft, unsaved-change guard, anti-double-submit. |

**Consistent button action-group example:**

```css
.action-group { display: flex; gap: var(--space-3); flex-wrap: wrap; }
.action-group > .btn { min-height: var(--control-height-md); min-inline-size: var(--action-min-inline); }
.action-group--equal > .btn { flex: 1 1 0; }
@media (max-width: 420px) { .action-group--equal > .btn { flex-basis: 100%; } }
```

Bukan semua button di seluruh website wajib selebar sama; **kesetaraan berlaku per kelompok dan level**. Aksi primary tetap paling jelas secara visual, destructive perlu pemisahan/konfirmasi, dan icon-only punya accessible name.

## 5. Konsep filtering Explore yang scalable

Kontrak publik terkini (8 November 2026): [product vision](docs/product-vision.md), [art direction](docs/art-direction.md), [motion](docs/motion-interaction.md), dan [page experience](docs/page-experience.md). [ADR 001](docs/adr/001-experience-authority.md) mencatat pembalikan keputusan; laporan 6–7 November adalah sejarah implementasi, bukan kontrak aktif yang saling menimpa.

Home memiliki satu kabinet Memory Archive 1634×963 dengan enam kunjungan published per halaman, preview dan foto cetak dari ID kunjungan yang sama. Tube resting vertikal DI LUAR sisi kiri pada ketinggian RESET, penyangga dua gusset/pelat baut; soket asli tetap pada left 2.8%, top 27%, width 4.25%, height 28.7%. Tidak ada label/tooltip/tutorial pickup visual atau panah pagination tambahan. Cue hanya sorot cap/refleksi kaca finite, statis pada reduced motion. Selector berindeks 32°, reset persegi ditekan tanpa rotasi, release valid mengunci/boot, reverse mematikan. Screen-reader help, retry, no-JS links, reduced-motion dan cancellation tetap. Rincian state/input/timing ada di kontrak motion.

Home memuat recent entries editorial dan explicit personal picks; tidak ada carousel otomatis, advanced filter atau discovery board kedua. Explore adalah interactive discovery board + photographic editorial Curiosity Index, bukan vending kedua atau katalog polos: kategori/kota/teknik, jenis hidangan/rekomendasi, Visit/Food/Place, search/filter/sort/view/pagination/URL/history lengkap. Visit/Place berbagi cinematic + tactile archival storytelling dengan hierarki cerita versus histori tempat, missing-data jujur dan original viewer on-demand.

Header/footer/focus memakai sumber bersama; tipografi publik dan admin sengaja berbeda. Shell publik P0-H maksimum 152rem, gutter responsif, atmosfer full-bleed dan footer editorial hangat; admin tetap 80rem dan reading measure 60–75ch. Kabinet dibatasi lebar sumber native 1634px. [Art direction](docs/art-direction.md), [bukti P0-H](docs/qa/p0-hardening/verification.md). Hover navigasi mengisi underline copper/amber kiri→kanan, dengan leave/re-entry/active/focus/reduced-motion; P0-1 mengembalikan kontrak ini 9 November 2026 termasuk standalone errors. [Verifikasi](docs/qa/navigation-hover-progress/verification.md). Home 5/Explore 3/Detail 3 adalah ambisi interaksi kualitatif; meaningful 3D diizinkan dengan akses/fallback dan biaya terukur. Admin minimal/system-font. HTTP error Editorial Bold/copy §1 tidak berubah.

**Masalah:** City, category, food type, cuisine, cooking style, price, rating, ingredient, allergen, tag, recommendation membuat UI cepat sesak. **Solusi:** munculkan yang paling sering dipakai; sisanya *progressive disclosure*. Pertahankan 3 tab Visit/Food/Place, tetapi definisi filter dan sort menyesuaikan konteks.

### Progressive disclosure (proposal)

1. **Always visible**: pencarian, tab Visit/Food/Place, sort, grid/list, tombol **Filter** dengan count badge ketika ada filter aktif.
2. **Quick filter chips (opsional bila ada data)**: Kota, Jenis makanan, Rentang harga yang sesuai tab, Rating; tampil tidak lebih dari 3–4 kelompok awal, tergantung viewport.
3. **Advanced filter panel**: bagian *Lokasi*, *Makanan*, *Cara memasak* (cuisine + cooking style **dibedakan**), *Anggaran & Rating*, *Preferensi bahan dan informasi alergen*, *Tag & rekomendasi*. Group by mental model pengunjung, bukan nama tabel.
4. **Applied filters summary**: chip yang bisa dihapus satu per satu, **Hapus semua filter**, jumlah hasil; sticky summary jika berguna tetapi jangan menutupi kartu.
5. **Mobile**: dialog/sheet full-height/near-full-height, kelompok accordion **bukan semuanya terbuka**, sticky `Tampilkan N hasil` + `Reset` dengan tombol ukuran konsisten; perubahan diterapkan saat Apply (untuk tidak reload tiap tap). Desktop boleh apply-on-change dengan loading ringan dan URL sinkron, tetapi harus punya satu kontrak shared.
6. **Avoid empty dimensions**: filter hanya tampil bila ada opsi pada data published eligible (taksonomi master yang belum digunakan tidak perlu memenuhi panel).

### Domain filter & semantik

| Tab | Filter harga | Filter rating | Filter taxonomy | Keterangan |
|---|---|---|---|---|
| Visit | **Total biaya kunjungan** | Overall visit | cuisine/style/ingredient diturunkan dari `EXISTS` item pada visit | Label jelas: “Kunjungan dengan setidaknya satu hidangan …”. |
| Food | **Harga per item** | Taste item | Cuisine/style/ingredient langsung dari item | Unit hasil = visit item snapshot. |
| Place | **Tidak tersedia P0** | Rata-rata visit published | Jika cuisine/style dipakai, berarti punya **minimal satu published item** yang match | Jangan mengklaim cuisine restoran permanen. |

- **Multi-select dalam satu kelompok**: OR (contoh Fried **atau** Grilled); **antar kelompok**: AND (Semarang **dan** Thai). Untuk `avoid_allergen` gunakan exclude, bukan OR matching biasa. Turunan `category` mengikutkan descendants, tetapi jangan termasuk `food_type` yang tidak berelasi.
- **Perpindahan tab**: preserve q, city, cuisine, style, ingredient, recommendation (jika relevan); reset page; sort tidak valid di-reset. Hapus harga ketika konteks berubah Food ⇄ Visit/Place **disertai UI feedback**, karena metrik berbeda. Fasilitas un/clear state eksplisit.
- **Draft/private** tidak ikut count opsi, suggestion, aggregation, maupun sample.
- **Allergen**: panel berjudul “Informasi bahan & alergen” dengan disclaimer terlihat ketika dipakai: `Menghilangkan item yang tercatat mengandung alergen. Item tanpa data mungkin tetap muncul—bukan jaminan aman.` Tidak pernah badge "allergy-safe". Tampilkan status `Tercatat mengandung`, `Berpotensi mengandung`, atau `Belum ada informasi`. Opsi strict confirmed free **tidak tersedia P0**.
- **Performance**: debounced search, cancel/stale response guard, server whitelist and eager load, URLs shareable, pagination. Skeleton real-wait baru ditunda ke P2 (choice 6A); P0-H mempertahankan busy/status/retry nyata dan hasil committed, tanpa delay palsu atau skeleton SSR.

### Labels and copy yang disarankan

| Konsep teknis | Label publik |
|---|---|
| `cuisine` | Asal/tradisi kuliner |
| `cooking_style` | Teknik memasak |
| `food_type` | Jenis hidangan |
| `ingredient` | Bahan yang diketahui |
| `avoid_allergen` | Sembunyikan yang tercatat mengandung… |
| visit total | Total pengeluaran |
| unit price | Harga saat dipesan |
| unknown ingredient/allergen | Informasi belum tersedia |

## 6. Admin: effortless data entry, bukan minim validasi

Choice 4A: permukaan kerja modern/minimal, system UI font untuk body dan heading, latar semantik polos, form/list jelas dan gerak rendah. Gunakan primitive aksesibel yang sama tanpa font display besar/atmosfer kabinet publik. Feedback tersimpan persisten diumumkan satu kali; toast tetap untuk pesan async yang relevan.

### Form behaviors

- **Quick Entry**: place combobox (recent choices, search, inline "Tambah tempat"), `visited_at` default waktu lokal sekarang, catatan ringkas opsional, status draft; tombol `Simpan draft` jelas. Tidak mewajibkan slug, rating, harga, ingredient, alergen atau foto.
- **Full Editor**: step/panel **Dasar → Hidangan → Ulasan → Foto → Pratinjau & Publish**. Bisa menambah beberapa item tanpa navigasi ke banyak halaman. Gunakan *repeater* item dengan "Tambah hidangan", collapse item completed dengan ringkasan.
- **Smart defaults**: slug dari judul/nama; tanggal kunjungan hari ini; status draft; quantity 1; item sort order bertambah. Auto nilai yang bukan fakta saja; **jangan** default rating 5, harga 0, cuisine Italia atau alergen aman.
- **Slug field**: default generated; tampil sebagai preview URL, `Edit slug` disclosure; manual override tetap tersimpan; perubahan slug published meminta peringatan konsekuensi redirect.
- **Smart select**: place, food_type, cuisine, cooking style, ingredient, label; dropdown search bila dataset cukup besar, `Create new` hanya untuk entitas yang aman diciptakan admin, dengan duplicate suggestions.
- **Ingredient/allergen**: input bahan yang diketahui opsional. Berdasarkan bahan, sistem dapat **menawarkan penanda review** untuk alergen, bukan mengklaim sebagai fakta otomatis. Perlu bukti/sumber untuk `contains`/`may_contain`. Field dapat kosong (=unknown), tidak mewajibkan admin menjadi ahli gizi.
- **Validation**: Draft bisa minim, publish gate memastikan `place`, tanggal/identitas kunjungan, konten layak tampil, dan semua foto visible memiliki derivative atau fallback. Jangan mewajibkan seluruh kriteria review jika tidak relevan. Error summary di atas form + inline messages, focus first invalid field.
- **Safety UX**: unsaved changes, server anti double submit, retry upload, optimistic UI hanya saat benar-benar terkonfirmasi; delete/revert status perlu modal dengan konsekuensi jelas.

### Admin dashboard

Satu CTA utama `Tambah kunjungan`, daftar `Draft perlu dilengkapi`, `Foto gagal diproses`, `Baru dipublikasikan`, quick links ke Place dan Taxonomy. Tidak memerlukan analytics card kosong demi dekorasi. Gunakan label yang langsung menjelaskan tugas yang dapat dikerjakan.

## 7. Media responsive dan original viewer

**Image contract:** `<picture>`/`<img srcset sizes width height>` untuk variant navigasi; hero LCP diprioritaskan dan tidak lazy; foto item dan cards di bawah fold lazy. Viewer memilih original setelah user click; fallback bila original belum siap/akses dicabut. Aspect ratio dan `object-fit` dipilih per konteks: hero `cover` dengan focal point admin opsional P1, foto makanan dapat `contain` di viewer. Jangan menggandakan file untuk cover.

**Gallery interaction:** viewer sebagai `dialog` satu fondasi; keyboard left/right untuk pindah foto jika fokus pada viewer (tidak mencuri shortcut input), Escape tutup, focus restore ke thumbnail, thumbnail selected state, loading/error original. Support touch swipe hanya bila implementasi tidak merusak scroll.

## 8. Reuse Blade / CSS / JS architecture

`@extends` + `@section/@yield` untuk layout klasik **atau** `<x-layout.public>` / `<x-layout.admin>` + slots untuk Blade components modern. Pilih **satu gaya layout utama** dan konsisten; tetap gunakan `<x-site.header>`, `<x-site.footer>`, `<x-ui.button>`, `<x-ui.modal>`, `<x-ui.alert>`, `<x-ui.toast-container>` dan domain components. `@include` boleh untuk potongan template sederhana; `@push/@stack` hanya untuk page-specific assets yang memang perlu, bukan JS inline yang tersebar.

```text
resources/
  views/
    components/
      layout/{public,admin}.blade.php
      site/{header,footer,breadcrumb}.blade.php
      ui/{button,field,combobox,modal,alert,toast-container,empty-state}.blade.php
      domain/{visit-card,food-card,place-card,rating,price,photo-viewer,filter-panel}.blade.php
    public/{home,explore,visit/show,place/show}.blade.php
    admin/{dashboard,visit/index,visit/editor,place/index,taxonomy/index}.blade.php
  css/{tokens,base,components,domain,pages}.css
  js/{core,components,admin,pages}/
```

Folder di atas usulan konseptual; Vite modules/entrypoints dipusatkan, tidak memuat skrip halaman di semua URL bila tak diperlukan. Hindari global event bus untuk semua kasus, duplicate JS handler, `onclick` inline, CSS selectors yang bergantung pada struktur halaman tak terkait. Jangan membuat `god component`.

## 9. States & accessibility behavior matrix

| Component / flow | Initial | Loading | Empty | Success | Error / recovery | Disabled |
|---|---|---|---|---|---|---|
| Explore | newest visits | loading results nyata | no result + reset | count/filter chips | retry / preserved URL | not applicable |
| Combobox | prompt + recent | loading suggestions | no match + optional create | selected tag | network/retry inline | explain reason |
| Upload | drop area | per-file progress | no photo yet | optimized ready | retry derivative; original safe | while submitting |
| Publish | draft + preview | single submit | missing data summary | badge Published | inline Alert + errors | publish gated with reason |
| Photo viewer | selected thumb | original loading | fallback variant | full-res | error + retry/close | prev/next at bounds |
| Modal | hidden | optional | optional | close + focus restore | inline alert | no background access |
| Toast | hidden | n/a | n/a | aria live summary | never sole critical error | n/a |

**Focus mechanics:** skip link → main; headings hierarchical; interactive cards accessible; modal focus trap/restore; no focus lost after dynamic filter; form error summary link target; animation reduced for `prefers-reduced-motion`; original image alt contextual; contrast on all foto overlays (not just design mocks).

## 10. QA visual & UX checklist

- [ ] Tanpa divider/gradient/dekorasi berlebihan; setiap surface/section memiliki fungsi.
- [ ] 320/375/768/1024/1440 px dan zoom 200%; no horizontal overflow; kebaca.
- [ ] Tombol satu group setara tinggi/min-width, termasuk loading text berubah; primary jelas.
- [ ] Filter kompleks bisa ditemukan pemula, mobile panel mudah ditutup/diterapkan/di-reset; URL/Back/Forward benar.
- [ ] Filter alergen tidak menjanjikan keamanan dan status unknown tidak disamarkan.
- [ ] Autocomplete keyboard/ARIA, clear, duplicate, zero-result, create new; edit slug manual tidak tertimpa.
- [ ] Gallery cards memakai derivative; original baru diminta setelah viewer; unpublish menolak akses baru.
- [ ] All loading/empty/error, focus trap, contrast, reduced motion, aria-live, keyboard tested.
- [ ] Tidak ada native JS alert/confirm; header/footer/layout tidak diduplikasi di setiap view.

## 11. Referensi standar

### Homepage editorial implementation

The earlier editorial option approvals remain registered in `docs/design/homepage-design-decisions.md` as history. Current composition is the cinematic Memory Archive described in §5 and the maintained `docs/page-experience.md` / `docs/motion-interaction.md` contracts: shared header/footer, a playable cabinet with removable power tube and printed real-visit photograph, chronological lead/supporting entries and actual recommendation picks. Home has no automatic carousels or advanced filters. Curiosity discovery tiles live on Explore. The F Refined torn-paper card remains the shared result/related component. Do not use the historical carousel/hero option report to override the current vending implementation package.


- W3C WCAG 2.2 Target Size (Minimum): https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum
- WAI ARIA Combobox Pattern: https://www.w3.org/WAI/ARIA/apg/patterns/combobox/
- WAI ARIA Dialog (Modal) Pattern: https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/
- web.dev Optimize Largest Contentful Paint: https://web.dev/articles/optimize-lcp
- FDA and European Commission references untuk allergen see `erd-database-specification.md` §8.

**Catatan keputusan:** pertanyaan review v1.1 telah dijawab oleh implementasi dan keputusan berikutnya: fonts publik self-hosted Cormorant Garamond/DM Sans, staged Apply/Cancel drawer, ingredient/allergen informational P0, header Beranda/Jelajah. Perubahan selanjutnya mengikuti ADR dan roadmap; pertanyaan historis bukan blocker baru.
