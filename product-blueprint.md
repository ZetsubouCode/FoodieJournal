# FOODIE — Product Blueprint

> **Versi:** Draft v1.1  
> **Tanggal:** 4 November 2026  
> **Status:** Baseline perencanaan; bukan deskripsi implementasi yang telah diuji  
> **Dokumen terkait:** `sitemap.md`, `erd-database-specification.md`, `design-system-specification.md`, `user-flow-wireframe.md`; semuanya rancangan untuk review, bukan bukti implementasi. Codex Implementation Plan (langkah #5) baru disusun setelah review.

## 1. Ringkasan produk

**Foodie** adalah *personal culinary journal* berbasis Laravel + MySQL. Satu pemilik mencatat dan menerbitkan pengalaman makan yang nyata—tempat, tanggal kunjungan, hidangan, harga pada saat pembelian, foto, review, rating, dan rekomendasi pribadi. Pembaca publik menikmati cerita dan menjelajah arsip tanpa registrasi atau login.

**Positioning:** jurnal editorial personal yang berorientasi pengalaman kunjungan; **bukan** marketplace, direktori restoran bersifat crowdsourced, atau platform review multi-pengguna.

**Janji produk:** “Temukan tempat dan hidangan melalui cerita kunjungan yang jujur, visual yang kuat, dan rincian pengalaman yang mudah dibandingkan.”

**Karakter visual:** dark cinematic editorial storytelling + bold experimental discovery. Fotografi dominan; tipografi kuat; komposisi eksploratif namun tetap nyaman, cepat, dan aksesibel. Palet charcoal, warm ivory, amber/copper; warna tidak ditetapkan langsung di tiap komponen, tetapi melalui token semantik. Whitespace dan komponen konsisten mendukung komposisi yang kaya, bukan halaman kosong atau layout kartu seragam. Aturan komposisi dan variasi kepadatan per jenis halaman ada di `design-system-specification.md` §1.

## 2. Fakta, keputusan kerja, dan usulan

### Keputusan yang sudah menjadi fondasi proyek

- Laravel + MySQL, Blade sebagai UI default; Alpine.js/Livewire bila interaksi memang membutuhkannya; tanpa SPA wajib.
- Bahasa Indonesia; harga dalam rupiah (IDR); zona waktu Asia/Jakarta.
- Hanya admin/pemilik mengelola konten; publik tidak memiliki akun.
- `place` memiliki banyak `visit`; `visit` memiliki banyak `visit_item`; satu kunjungan memiliki galeri foto dan foto dapat ditautkan ke item.
- Status konten `draft`, `private`, `published`; hanya `published` muncul di permukaan publik, statistik publik, SEO, dan API publik apabila tersedia.
- Rating 1–5 dalam kelipatan 0,5; nilai kosong tidak dianggap nol.
- Rekomendasi editorial berbeda dari nilai rating numerik.
- Semua **nama tabel MySQL singular**; constraint, FK, indeks, dan historical snapshot dipertahankan.
- Komponen Custom Alert, Modal/Dialog, dan Toast menjadi fondasi sejak awal, bersama token, primitives, state, dan aturan aksesibilitas.

### Usulan operasional yang dipakai dalam draf ini

- **U1:** Entitas `place` baru terlihat publik setelah memiliki setidaknya satu `visit` berstatus `published`. Ini mencegah publik melihat tempat yang belum pernah diceritakan.
- **U2:** Laman `/food/{slug}` menunjuk **jenis hidangan spesifik** (`food_type`: mis. Mie Ayam), sementara `/category/{slug}` menunjuk **kelompok** hierarkis ringan (`Makanan → Mie`); jangan membuat kategori leaf dengan makna sama persis sebagai food_type. Nama menu di `visit_item` tetap snapshot teks bebas.
- **U3:** Label rekomendasi diterapkan pada `visit` terlebih dahulu; rekomendasi tempat dapat diturunkan dari kunjungan yang berlabel, tanpa menyamakan rekomendasi dan rating.
- **U4:** `private` berarti sengaja disimpan hanya untuk admin walaupun datanya sudah lengkap; `draft` berarti masih dalam proses penulisan. Keduanya tidak dapat diakses publik.
- **U5:** Kunjungan yang tidak memiliki rating tetap boleh `published` dan muncul sebagai **Belum Dinilai**, tetapi tidak masuk pembagi perhitungan rating place.
- **U6:** Halaman city/food/style/cuisine discovery khusus hadir bertahap; inti pencarian/filter adalah satu layanan/query yang dipakai bersama Explore agar tidak terjadi logic drift.
- **U7:** `cuisine` (tradisi/regional kuliner, mis. Thai, Italian) **terpisah** dari `cooking_style` (teknik pengolahan multi-tag, mis. fried/grilled/boiled). `Asian` adalah label wilayah luas; `Western` label editorial yang ambigu dan tidak otomatis setara dengan kawasan geografis tertentu.
- **U8:** Bahan (`ingredient`) dicatat bila diketahui; penanda `allergen` mempunyai status *contains/may_contain/unknown* dengan sumber informasi. Data kosong **tidak boleh** diartikan aman/bebas alergi. Filter hanya dapat menyembunyikan kandungan yang *tercatat*, bukan menjamin keamanan.
- **U9:** Foto disimpan dengan satu original berkualitas tinggi yang dapat diminta saat perbesar, sementara navigasi memakai derivative terkompresi; semua varian wajib patuh pada status publish dan pengelolaan media.
- **U10:** Alur admin mengutamakan input minimum, auto slug editable, tanggal default lokal hari ini, autocomplete, inline create, sensible defaults, dan publish gate. Tidak memberi default pada fakta yang tak diketahui (rating/harga/alergen).

Semua usulan di atas **masih untuk review**, termasuk ruang lingkup filter alergen dan model cuisine. Lihat tiga spesifikasi terkait sebelum mengunci migration atau memulai instruksi eksekusi Codex.

## 3. Sasaran dan non-sasaran

### Sasaran utama

1. Membuat pencatatan kunjungan **mudah dilakukan** tanpa harus mengisi semua detail saat itu juga.
2. Menjaga sejarah: harga, tanggal, rasa, dan pengalaman dari satu kunjungan tidak otomatis berubah mengikuti kunjungan berikutnya.
3. Membuat arsip menyenangkan untuk dijelajahi melalui tempat, makanan, kota, gaya masak, rating, harga, dan rekomendasi personal.
4. Menghadirkan website publik cepat, berkarakter editorial, responsif, dan aman dari kebocoran konten yang belum dipublikasikan.
5. Menjaga implementasi sederhana, modular, mudah diuji, murah dioperasikan, dan siap bertumbuh.

### Bukan sasaran untuk MVP

- Registrasi publik, komentar, rating pembaca, atau upload foto dari pengunjung.
- Booking restoran, pemesanan makanan, pembayaran, atau integrasi delivery.
- Algoritme rekomendasi personal berbasis akun/AI, gamifikasi, atau feed sosial.
- Integrasi Google Maps berbayar; cukup link menuju Google Maps jika tersedia.
- Klaim makanan “aman untuk alergi”, sertifikasi bebas alergen, analisis laboratorium, atau diagnosis medis. Data Foodie bersifat catatan pribadi dan mungkin tidak lengkap.
- SPA, dashboard analytics kompleks, headless CMS, atau aplikasi mobile native.
- Pembuatan entitas menu global dengan harga berjalan yang menggantikan snapshot item kunjungan.

## 4. Pengguna dan kebutuhan

| Aktor | Tujuan | Tugas utama | Benturan yang harus dihindari |
|---|---|---|---|
| Pembaca kasual | Mencari ide makan | Masuk Home → lihat pengalaman → buka kunjungan/tempat | Halaman ramai, CTA tak relevan, foto lambat |
| Pembaca eksploratif | Membandingkan opsi | Explore → tab Food/Place/Visit → filter/sort → detail | Filter hilang saat pindah mode; harga ambigu |
| Admin/pemilik | Mencatat cepat dan akurat | Buat/pilih tempat → kunjungan → hidangan → foto → publikasi | Form panjang, input wajib tidak relevan, double submit |
| Admin/pemilik | Memelihara arsip | Edit draft/private, review data hilang, rapikan taksonomi | Relasi rusak, foto duplikat, data historis berubah |

**Jobs to be done:**

- Ketika baru selesai makan, admin ingin menyimpan fakta minimum agar pengalaman tidak terlupakan, lalu melengkapi review/foto nanti.
- Ketika pembaca penasaran dengan makanan tertentu, ia ingin melihat pengalaman nyata dan kisaran harga saat kunjungan, tanpa tertukar dengan total belanja satu kunjungan.
- Ketika pembaca menemukan tempat menarik, ia ingin melihat lokasi dan **semua riwayat kunjungan yang dipublikasikan**, bukan hanya satu skor agregat tanpa konteks.

## 5. Domain model konseptual (belum ERD final)

| Konsep | Kegunaan | Aturan penting |
|---|---|---|
| `place` | Identitas tempat makan | Nama/lokasi secukupnya; kota/provinsi; alamat, Maps URL, koordinat boleh tidak lengkap |
| `visit` | Satu pengalaman pada tanggal tertentu | Punya status, cerita, ulasan terstruktur, total biaya, foto, label rekomendasi, dan rating per kunjungan |
| `visit_item` | Hidangan/minuman yang dibeli | Nama menu snapshot; kuantitas opsional; harga satuan saat dibeli; rating rasa dan catatan; bisa muncul di tempat lain tanpa menautkan harga historisnya |
| `category` | Kelompok kategori hierarkis ringan | Contoh `Makanan` → `Mie`; `Minuman` → `Kopi`; hindari duplikasi leaf yang sama makna dengan `food_type` |
| `food_type` | Jenis hidangan spesifik lintas tempat | Contoh `Mie Ayam`, `Kopi Susu`; terkait kategori yang lebih umum; bukan nama menu merchant |
| `cuisine` | Tradisi/regional kuliner | Mis. `Asia → Thai/Japanese`, `Eropa → Italian`; bisa beberapa label item untuk fusion; `Western` hanya label editorial bila dipakai |
| `cooking_style` | Teknik pengolahan makanan | Multi-tag per item, misalnya `fried`, `grilled`, `boiled`; **bukan** cuisine |
| `ingredient` | Bahan hidangan yang diketahui | Input opsional; tidak berarti resep lengkap; satu item boleh memiliki banyak bahan |
| `allergen` + evidence | Informasi potensi alergen | Sumber dan status `contains`, `may_contain`; tanpa record = `unknown`, **bukan free-from** |
| `tag` | Tag editorial bebas | Tidak menggantikan kategori, style, maupun label rekomendasi |
| `recommendation_label` | Penilaian pilihan editorial | Mis. Must Try, Hidden Gem, Worth Revisit; mandiri dari skor rating |
| `media` | Aset gambar satu galeri per kunjungan | File tidak digandakan untuk cover atau untuk asosiasi dengan item; perlu metadata dan visibilitas |
| Relasi `visit_item`–`media` | Foto makanan tertentu | Satu item boleh mempunyai banyak foto; asosiasi tidak membuat salinan berkas |

**Catatan pemodelan:** tabel, pivot, constraint, dan kolom kandidat dirinci di `erd-database-specification.md`. Food type dan category dipisah secara konseptual supaya tidak redundan. Relasi cuisine dan cooking_style berada pada **visit item**, bukan klaim tetap bahwa seluruh place menyajikan satu cuisine. Seluruh nama tabel MySQL singular.

## 6. Aturan bisnis yang tidak boleh berubah tanpa keputusan sadar

### 6.1 Rating

- Nilai rating valid: 1, 1,5, 2, 2,5, 3, 3,5, 4, 4,5, 5; `NULL` berarti belum dinilai.
- **Taste visit** = rata-rata rating rasa dari `visit_item` yang nilainya terisi. Item tidak dinilai dikecualikan.
- **Overall visit** = rata-rata aritmetika dari kriteria yang tersedia: taste (hasil agregat item), service, atmosphere, cleanliness, value for money. Tidak berbobot; semua yang `NULL` dikecualikan.
- Jika tidak ada kriteria terisi, tampilkan **Belum Dinilai** dan overall tetap `NULL`.
- **Rating place** = rata-rata overall dari `visit` berstatus `published` dan dinilai. Tampilkan skor 1 desimal serta **jumlah kunjungan yang dinilai** (lebih akurat daripada “jumlah reviewer” karena penulisnya satu orang).
- Jangan melakukan pembulatan di setiap tahapan rata-rata; simpan/hitung secara presisi lalu bulatkan hanya untuk tampilan. Field rating input tetap kelipatan 0,5; hasil agregasi boleh bernilai desimal lainnya.
- Riwayat setiap visit tetap dapat dilihat secara terpisah; rating lama tidak ditimpa rating baru.

**Contoh:** rating rasa tiga item [4,5; NULL; 3,5] menghasilkan taste visit 4,0. Jika service 3,5 dan kriteria lain kosong, overall visit adalah 3,75 dan tampilan 3,8.

### 6.2 Harga

- `visit_item` menyimpan **harga pada saat dibeli**, bukan mengacu ke harga menu terbaru.
- Total yang dikeluarkan pada `visit` terpisah dari harga item, karena bisa mengandung pajak, biaya layanan, pembulatan, atau item lain.
- Jangan otomatis menyimpulkan total visit sama dengan penjumlahan item. Boleh tampilkan subtotal item sebagai informasi tambahan jika input lengkap.
- Filter harga pada tab **Food** menggunakan harga item; tab **Visit** menggunakan total kunjungan; tab **Place** harus memakai metrik harga yang diberi label jelas atau tidak menampilkan filter harga pada MVP.
- Semua harga ditampilkan sebagai IDR; jumlah/nilai opsional tidak otomatis dianggap Rp0.

### 6.3 Visibilitas, publikasi, dan query

| Status | Admin | Publik | Search/filter publik | Sitemap/SEO/API publik |
|---|---|---|---|---|
| `draft` | CRUD, preview | Tidak | Tidak | Tidak |
| `private` | CRUD, preview | Tidak | Tidak | Tidak |
| `published` | CRUD | Ya | Ya | Ya, jika rute memang diindeks |

- Penjagaan visibilitas berlaku pada controller/query backend, bukan hanya tombol atau menu.
- Item, foto, kategori, counter, agregat, hero featured, metadata OG, JSON-LD, dan endpoint publik harus berasal dari konten `published` yang relevan.
- Slug/ID sebuah visit nonpublik yang ditebak harus menghasilkan 404 bagi pengunjung; admin terautentikasi dapat membuka **rute preview admin yang terproteksi**.
- Jika visit published dikembalikan ke draft/private, hapus ia dari seluruh keluaran publik dan invalidasi cache relevan.
- File asli nonpublik tidak boleh terekspos melalui URL storage publik yang bisa ditebak; strategi disk/derivative/akses media ditetapkan sebelum upload dibangun.

### 6.4 Bahan, alergen, dan keselamatan informasi

- FDA AS mengenal **9 kelompok major food allergens**, sedangkan aturan deklarasi Uni Eropa menyebut **14 kategori**. Foodie menggunakan keduanya sebagai acuan membuat taxonomy, **bukan** memaksakan label keselamatan medis atau hukum pelabelan kemasan pada jurnal pribadi. Daftar FDA termasuk milk, eggs, fish, crustacean shellfish, tree nuts, peanuts, wheat, soybeans, sesame; daftar EU menambah antara lain celery, mustard, lupin, molluscs, sulphites dan kelompok cereals containing gluten. `wheat` (AS) **tidak sama** dengan seluruh kelompok gluten cereals (EU).
- Bahan dan alergen dicatat per hidangan dengan bukti/sumber bila ada. Status: **tercatat mengandung** (`contains`), **berpotensi mengandung** (`may_contain`), atau **belum diketahui** (`unknown`, jika tidak dicatat). Tidak boleh ada auto inferred “aman” atau “bebas alergen” dari daftar bahan yang belum lengkap.
- Fitur filter awal “Sembunyikan yang tercatat mengandung X” mengeluarkan `contains` dan `may_contain` namun item berstatus unknown bisa tetap tampil **dengan warning yang nyata**. Ini bukan filter aman untuk penderita alergi. Strict `hanya terverifikasi bebas X` dikeluarkan dari MVP, karena butuh sumber validasi, cross-contact handling dan sistem pembuktian yang belum dimiliki.
- Tampilkan pesan saat filter digunakan: **“Informasi bahan/alergen mungkin tidak lengkap. Hasil bukan jaminan bebas alergen; konfirmasi langsung kepada penjual.”** Jangan mengarang dari nama menu atau foto. Alergi vs intoleransi vs celiac tidak boleh disamakan.
- Referensi 2023–2026 (riset primer/otoritas): [FDA major nine](https://www.fda.gov/food/buy-store-serve-safe-food/food-allergies-what-you-need-know), [European Commission 14 categories, 2026](https://food.ec.europa.eu/food-safety/campaign-2026/allergies_en), [EAACI diagnosis, 2023](https://pubmed.ncbi.nlm.nih.gov/37815205/), [EAACI management, first published 2024/issue 2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC11724237/). Penjelasan seed dan batas penggunaannya ada di ERD §8.

### 6.5 Tempat dan kelengkapan data

- Dukung warung, kaki lima, pedagang berpindah, dan tempat yang belum punya alamat lengkap.
- Tidak semua field lokasi wajib; data tempat paling sedikit harus punya nama yang berguna bagi admin, dengan aturan field wajib ditetapkan berdasarkan alur penyimpanan draft.
- Google Maps URL dan koordinat opsional. Jika tidak tersedia, tidak munculkan tombol Map yang palsu.
- Satu tempat bisa memiliki banyak visit, dengan harga, opini, dan rating berbeda dari waktu ke waktu.

## 7. Ruang lingkup fitur menurut prioritas

**P0 = wajib untuk rilis pertama (MVP); P1 = berikutnya; P2 = peluang selanjutnya.** Prioritas adalah usulan roadmap, bukan indikasi sudah diimplementasikan.

| ID | Fitur | Prioritas | Keterangan |
|---|---|---|---|
| PUB-01 | Home Memory Archive | P0 | Kabinet memori terkurasi, kunjungan terbaru editorial, rekomendasi pribadi, pintu Explore terfilter |
| PUB-02 | Explore tab Visit/ Food/ Place | P0 | Default Visit terbaru, keyword, filter dasar, sort per konteks, grid/list, URL persisten |
| PUB-03 | Visit detail | P0 | Cerita, rating terstruktur, item, harga historis, galeri, kesimpulan, link tempat |
| PUB-04 | Place detail | P0 | Info lokasi, Maps jika tersedia, seluruh riwayat published, rating agregat dan skor tiap kunjungan |
| PUB-05 | Discovery kota/food/style berbasis Explore | P0 | Filter/link terarah tanpa wajib membuat template halaman unik dulu |
| PUB-06 | Landing khusus kota/food/style/category | P1 | Halaman editorial atau SEO terdedikasi yang menggunakan query Explore bersama |
| PUB-07 | Filter lanjutan (cuisine/style/bahan/alergen/tag/rekomendasi) | P0 | Progressive disclosure; informasi alergen **bukan safety guarantee**, only known-exposure exclusion; paginasi dan query visibility benar |
| PUB-09 | Penjelasan informasi bahan/alergen | P0 | Per item; tampilkan sumber/unknown/contains/may_contain dan pesan batasan |
| PUB-08 | API publik read-only | P2 | Hanya jika ada consumer nyata; tak boleh bocor private/draft |
| ADM-01 | Admin auth & authorization | P0 | Tanpa registrasi publik; middleware, session aman, rate limit login |
| ADM-02 | Dashboard konten | P0 | Ringkasan draft, private, published, dan entri butuh kelengkapan |
| ADM-03 | CRUD place | P0 | Pilih/buat cepat, autocomplete, deteksi duplikat, validasi adaptif |
| ADM-04 | CRUD visit + review + item | P0 | Quick Entry & Full Editor berbagi komponen, search-select, auto slug editable, default datetime Asia/Jakarta, inline-create dan form bertahap |
| ADM-05 | Upload/galeri/cover + derivative | P0 | Original private high-quality untuk viewer; optimized varian untuk navigasi; cover/reference tanpa menggandakan file |
| ADM-06 | Pengelolaan status & preview | P0 | Draft/private/published, pemeriksaan publish, akses backend |
| ADM-07 | Taksonomi/label/tag/cuisine/bahan/alergen | P0 | Taxonomy searchable, inline create yang tepat, curated allergen seed; no redundant classification |
| ADM-08 | Bulk import/organisasi lanjutan | P2 | Hanya bila kebutuhan tercatat |
| SYS-01 | Design system dan komponen reusable | P0 | Tokens, button action-group parity, autocomplete, filter sheet, card, Alert/Modal/Toast, empty/error/skeleton, Blade layouts/header/footer shared, no AI-slop |
| SYS-02 | Testing & keamanan | P0 | Aturan rating, histori, visibilitas, upload, filter, mobile, akses, regression |
| SYS-03 | SEO & halaman error | P0 | Metadata published, canonical, sitemap.xml, 403/404/419/429/500/503 |
| SYS-04 | Backup/restore DB + media | P0 | Proses pemulihan terdokumentasi dan diuji, tanpa layanan berbayar wajib |

### Definisi MVP secara praktis

MVP selesai bila pemilik dapat membuat tempat → menyimpan kunjungan → mengisi hidangan/rating/harga/foto → preview → menerbitkan → publik bisa menemukannya di Home/Explore → melihat rincian visit dan riwayat place; konten privat aman, navigasi mobile layak, dan perhitungan rating/harga benar.

## 8. Alur pengguna utama

### 8.1 Alur publik: menemukan pengalaman

`Home → Explore (default Visit terbaru) → search/filter/tab → Visit Detail → Place Detail → riwayat Visit lain`

Alternatif: `Home Memory Archive → koleksi terkurasi → amplop/detail atau Explore terfilter`. Explore memuat Curiosity Index untuk kota, kategori, hidangan, teknik dan rekomendasi.

**Desain interaksi:**
- Header sederhana: logo Home, Jelajah (Explore), dan akses eksplorasi kota/rekomendasi yang bisa masuk sebagai section Explore dahulu.
- Semua kartu memiliki area clickable yang jelas; jangan menambahkan link “Lihat Detail” redundant bila kartu sudah interaktif dan aksesibel.
- Filter rumit muncul bertahap: search, tab, sort, dan filter primer terlihat; advanced panel/sheet memuat cuisine, teknik, bahan, alergen, tag dan rekomendasi; active filter chips + reset selalu jelas.
- Perpindahan `Visit/Food/Place` menjaga query yang kompatibel; filter yang tidak berlaku pada tab baru tidak menyebabkan data salah atau UX membingungkan.
- Browser Back/Forward mempertahankan tab, keyword, filter, sort, dan mode melalui URL.
- Empty state menjelaskan mengapa hasil tidak ada dan menawarkan *Reset filter*.

### 8.2 Alur admin: Quick Entry

`Login → Dashboard → Tambah Kunjungan → pilih/buat Place → tanggal → catatan ringkas → simpan Draft`

- `visited_at` otomatis diisi waktu lokal Asia/Jakarta saat form baru dibuka (editable); default status `draft`; place dipilih melalui autocomplete recent/searchable dengan opsi buat tempat inline. Slug dari nama/title otomatis; admin boleh **Edit slug** tanpa field wajib.
- Opsi menambahkan minimal satu item, biaya, dan foto **jika sudah tersedia**, bukan syarat agar draft bisa disimpan. Jangan default rating 5, harga Rp0, atau allergen-free.
- Konfirmasi penyimpanan dengan toast; bila terjadi kegagalan penting, tampilkan inline Alert.
- Alur bisa diteruskan ke Full Editor tanpa menduplikasi data atau logika validasi.

### 8.3 Alur admin: Full Editor dan publish

`Draft/Visit baru → detail tempat & waktu → item & taxonomy & harga → review/rating → media/cover → preview → validasi publish → Publish`

- Kelompokkan ke section/step pendek alih-alih satu form panjang.
- Autocomplete tempat/jenis/cuisine/teknik/bahan, multi-select yang sesuai, dan inline create dengan duplikasi check. Pengisian bahan/alergen adalah bagian **opsional yang bisa dibuka** agar form utama tidak menakutkan.
- Gunakan satu foundation modal untuk picker/konfirmasi, bukan `window.alert/confirm`.
- Publish harus atomik, anti-double-submit, berizin, dan menunjukkan error jika gagal.
- Foto dihapus/diubah memakai konfirmasi dan pengelolaan orphan yang aman.

## 9. Arsitektur informasi dan pengalaman publik

- **Home** adalah Memory Archive terkurasi; kontennya berasal dari published visits. **Explore** adalah Curiosity Index dan penelusuran aktif. **Visit Detail** adalah Culinary Dossier dengan cerita, foto, harga historis dan penilaian. Arsitektur visual terkini tercatat di docs/cinematic-redesign.md.
- **Explore** adalah satu sumber discovery dan canonical pengalaman browsing, dengan tab `Visit`, `Food`, `Place`.
- **Visit** adalah objek cerita utama dan default hasil Explore.
- **Place** adalah konteks lokasi serta arsip beberapa pengalaman, bukan satu review statis.
- **Food** adalah sudut pandang hidangan/jenis; harga yang ditampilkan adalah harga snapshot item pada visit terkait.
- **Cuisine** mencatat asal/tradisi kuliner, berbeda dari **cooking style**; bahan & alergen hanya dideskripsikan berdasarkan data yang diketahui.
- **City/Category/Style** merupakan lensa eksplorasi, bukan database konten yang terpisah.
- Label rekomendasi memberikan konteks kurasi personal, bukan sertifikat berbasis skor.

Detail URL dan navigasi terdapat di `sitemap.md`.

## 10. Desain sistem dan kualitas UX

### 10.1 Fondasi desain wajib sebelum komponen halaman

| Lapisan | Deliverable |
|---|---|
| Tokens | Surface/background, text primary/secondary/muted, actions primary/secondary/destructive, status info/success/warning/error, border, font, spacing, radius, shadow, z-index, focus, motion, breakpoint |
| Primitives | Button dengan shared sizing/variants, input, textarea, select/autocomplete keyboard-accessible, checkbox, badge/chip, card, rating, tabs, skeleton, empty/error states |
| Feedback | Custom Alert inline, Custom Modal/Dialog aksesibel, Toast service terpusat dengan timeout/stacking/dismiss |
| Domain | Visit/Food/Place Card, Rating/Price, Responsive Photo Gallery + original viewer, Filter quick-bar/advanced sheet, Status/Allergen Info Badge, Admin smart form |
| Pages | Home, Explore, detail, admin; halaman tidak menyalin CSS/JS atau validasi komponen secara manual |

### 10.2 Perilaku yang harus diuji

- WCAG 2.2 AA sebagai target: kontras teks normal minimal 4.5:1; UI penting minimal 3:1; indikator fokus jelas; HTML semantik; keyboard/touch.
- Modal memiliki ARIA, focus trap/restore, Escape/close, scroll lock, dan konfirmasi destruktif yang eksplisit.
- Toast tidak menjadi satu-satunya pemberitahuan error penting; gunakan Alert inline untuk hal yang perlu tindakan.
- State halaman/form: initial, loading, empty, success, error, disabled, updating.
- Skeleton hanya untuk loading nyata dan ukurannya menekan layout shift; tidak membuat animasi palsu.
- Micro-interaction singkat memakai transform/opacity seperlunya, dukung `prefers-reduced-motion`.
- Mobile-first; foto besar boleh dramatis tetapi tombol, filter, dan isi tetap usable.
- Optimalkan LCP/INP/CLS dengan pengukuran, `srcset/sizes` dan `width/height`; lazy gambar non-kritis, prioritaskan hero LCP **tanpa** lazy; kunjungan memakai variant compressed, klik perbesar meminta original.
- Terapkan standar kualitas editorial: **whitespace + type hierarchy** lebih utama daripada deretan divider `---`, dekorasi/gradient hanya jika fungsional, CTA tanpa flavor text, button selevel **seragam tinggi dan min-width dalam kelompok aksi**. Hindari fixed width berbeda semata akibat panjang teks.
- Header/footer/sidebar/section berulang dibangun sebagai **Blade components**, partial `@include` atau layout `@extends/@section/@yield`/slots; tidak menyalin markup, CSS, dan event listener per halaman. Shared CSS tokens→primitives→domain→page; JS ES modules modular.

## 11. SEO, akses, dan performa

- Publikasi semantik: judul, deskripsi, heading, breadcrumb, canonical, Open Graph, dan sitemap.xml hanya dari data eligible published.
- `robots.txt` bukan mekanisme perlindungan private; otorisasi backend dan media wajib melindungi konten.
- Slug human-readable dan unik; **generate otomatis** dengan suffix konflik, editable admin; setelah manual override tidak ditimpa, perubahan slug published membuat redirect 301 bila target masih published.
- Index query/filter diputuskan setelah melihat kebutuhan; hindari indeksasi setiap kombinasi URL filter agar tidak menimbulkan duplicate content.
- Laravel: Form Request, Policy/Gate, eager loading, pagination, query scopes published, transaksi atomik saat perlu, index sesuai query, cache hanya jika invalidation jelas.
- Backup **database dan media** disertai prosedur restore; penghapusan media harus aman dari broken references.
- HTTP error page branded: 403, 404, 419, 429, 500, 503; tidak menampilkan stack trace/secret.

## 12. Risiko dan guardrail

| Risiko | Konsekuensi | Penanganan |
|---|---|---|
| Private/draft bocor melalui query, image URL, atau SEO | Pelanggaran privasi | Scope backend terpusat, media terproteksi, negative tests dari route & API |
| Rating agregat salah akibat `NULL`/rounding | Informasi menyesatkan | Test fixture 0/1/banyak item; hitung presisi; pembulatan sekali |
| Total visit disamakan dengan item subtotal | Sejarah biaya salah | Field, label, query filter, dan test terpisah |
| Taksonomi dan nama menu mencampur domain | Duplikasi/UX tak konsisten | Batasan jelas antara kategori, jenis, snapshot menu, teknik, tag |
| Galeri dan cover menggandakan file | Boros storage, error hapus | Satu media record, reference/pivot, lifecycle file terkontrol |
| Foto full original selalu dikirim ke kartu | Navigasi berat | Private original + compressed derivative; `srcset`, no-lazy LCP, protected viewer; retry gagal proses |
| Slug dan data dasar semuanya wajib manual | Admin lama mencatat | Slug otomatis editable, now Asia/Jakarta, default draft, autocomplete dan inline create |
| Kategori/cuisine/teknik bercampur | Filter menyesatkan dan input rangkap | Peran taxonomy eksplisit, category broad vs food_type specific |
| Filter alergen dilabeli “aman” padahal info kosong | Risiko kesehatan | Multi-state exposure, unknown label, disclaimer, exclude known only |
| UI generik/overdecorated | Lemah secara karakter dan usability | Whitespace/hierarchy editorial, tidak banyak divider/gradient, button parity |
| Filter semakin kompleks dan lambat | Query berat, pengalaman buruk | Reuse query service, eager load, pagination, indeks dari query nyata |
| Visual sinematik mengorbankan aksesibilitas | Sulit digunakan, lambat | Kontras/fokus, mobile-first, reduced motion, audit performa |
| MVP tumbuh tanpa batas | Tidak kunjung rilis | Kunci P0; semua fitur lain masuk P1/P2 dengan alasan |

## 13. Acceptance criteria rilis MVP

- [ ] Pengunjung tanpa login dapat membaca Home, Explore, Visit Detail, dan Place Detail dari konten published.
- [ ] Tab default Explore adalah kunjungan terbaru; tab, keyword, filter, sort, dan grid/list bekerja sesuai konteks serta persisten di URL.
- [ ] Semua rute detail dan agregat publik mengecualikan draft/private, termasuk foto, SEO, statistik, dan pencarian.
- [ ] Place dapat memiliki beberapa visit; riwayat menampilkan tiap skor dan agregat yang dihitung hanya dari published visit yang dinilai.
- [ ] Rating kelipatan 0,5 tervalidasi server; nilai NULL diabaikan; seluruh null menjadi “Belum Dinilai”; hasil ditampilkan 1 desimal.
- [ ] Item price snapshot dan visit total dapat berbeda; label dan filter tidak saling tertukar.
- [ ] Admin login, membuat/mengubah place, visit, item, review, taxonomies, dan status konten; tidak ada registrasi publik.
- [ ] Quick Entry menyimpan draft tanpa mewajibkan detail tidak relevan; Full Editor melanjutkan catatan yang sama.
- [ ] Admin slug otomatis editable dengan collision/rename rule, default visited_at Asia/Jakarta, search-select place/taxonomy, inline create tidak kehilangan form state, anti-double-submit.
- [ ] Category (broad), food_type (specific), cuisine (regional), cooking_style (multi), ingredient dan allergen tidak dipertukarkan; filter masing-masing mengembalikan data konsisten.
- [ ] Filter alergen mengecualikan known contains/may contain dan memberi label unknown; tidak ada klaim "bebas/aman". Referensi ilmiah dicatat dan ada disclaimer publik.
- [ ] Foto card/galeri memakai optimized variant, viewer original, original aman bila processing gagal, akses baru ditutup ketika unpublished.
- [ ] Layout/header/footer reusable Blade; tidak ada gradient/divider/filler berlebih, button sederajat konsisten, mobile filter drawer aksesibel.
- [ ] Foto galeri dapat dikaitkan ke item, cover dapat dipilih tanpa menyalin berkas, dan penghapusan tidak menyisakan orphan tak terkelola.
- [ ] Komponen reusable Alert/Modal/Toast digunakan konsisten, aksesibel, dan tidak bergantung pada dialog browser bawaan.
- [ ] Responsive, keyboard navigation, focus visible, reduced motion, empty/loading/error states, dan branded HTTP errors diuji.
- [ ] Test inti untuk publish/unpublish, akses, perhitungan rating, harga historis, upload, filters, dan regression lulus; backup/restore punya prosedur nyata.

## 14. Urutan pengerjaan yang direkomendasikan

1. **Review domain dan halaman:** Blueprint v1.1 + Sitemap v1.1; review keputusan U1–U10 dan isu D1–D7 di spesifikasi ERD.
2. **Review ERD & aturan query:** `erd-database-specification.md` — tabel singular, taxonomy cuisine/ingredient/allergen, media lifecycle, status, rating/harga.
3. **Review Design System:** `design-system-specification.md` — tokens, reusable Blade, admin smart input, progressive filters, photo variants, no AI-slop.
4. **Review User Flow & Wireframe:** `user-flow-wireframe.md` — desktop/mobile Home, Explore, filter, Visit, Place, Quick Entry/Full Editor, states & QA.
5. **Setelah disetujui:** buat implementation instructions untuk Codex dengan scope/acceptance/testing; tidak otomatis eksekusi dari dokumen review.
6. **Implementasi bertahap:** admin+media→publik→QA/security/backup/SEO, setelah Codex menginspeksi repo nyata.
7. **P1:** landing city/food/category/cuisine/style dengan logic Explore bersama, bila dibutuhkan.

## 15. Keputusan yang perlu ditutup sebelum ERD final

1. Apakah tempat tanpa visit published memang tidak boleh tampil publik? (Default: **tidak tampil**.)
2. Apakah pemisahan **category broad** dan **food_type specific** diterima? (Default: terpisah, tetapi hindari isi dua field dengan makna sama.)
3. Pada level mana label rekomendasi ditempel—visit saja atau juga place/item? (Default: **visit** sebagai sumber utama.)
4. Apakah total biaya visit boleh kosong saat publish? (Default: **boleh**, tampilkan “Harga belum dicatat”; tidak dihitung sebagai Rp0.)
5. Bagaimana hak akses file media draft/private dan perpindahannya ketika publish/unpublish? (Default: **file privat dijaga**, derivative publik diatur melalui lifecycle yang aman.)
6. Perlukah halaman discovery khusus sejak hari pertama? (Default: **tidak**; link diarahkan ke Explore dengan filter, lalu landing khusus di P1.)
7. Apakah cuisine item-level multi-select dan hierarchy pendek sesuai kebutuhan? (Default: **ya**; Western hanya editorial, jangan otomatis dari Italian.)
8. Apakah ingredient/allergen sebagai keterangan informasional dan filter known-exposure (bukan "aman alergi") disetujui untuk P0? (Default: **ya**.)
9. Apakah upload menyimpan original private dengan optimized derivatives untuk card, viewer original authorized, dan retry pipeline? (Default: **ya**.)
10. Apakah admin menggunakan autocomplete+inline create, auto slug editable dan default date/time Asia/Jakarta? (Default: **ya**; nonfakta tetap nullable.)

---

**Kriteria perubahan blueprint:** perubahan keputusan bisnis, domain, status, formula, atau navigasi publik diperbarui di dokumen ini dan `sitemap.md` sebelum menginstruksikan Codex, agar tidak ada dua sumber kebenaran yang bertentangan.
