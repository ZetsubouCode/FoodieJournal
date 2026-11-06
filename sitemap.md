# FOODIE — Sitemap & Information Architecture

> **Versi:** Draft v1.1  
> **Tanggal:** 4 November 2026  
> **Status:** Proposed navigation/routes, belum diverifikasi dengan repository  
> **Acuan:** `product-blueprint.md`, `erd-database-specification.md`, `design-system-specification.md`, `user-flow-wireframe.md`  
> **Konvensi:** path adalah desain URL, **bukan** bukti route Laravel sudah ada.

## 1. Prinsip sitemap

1. **Visit-first:** pengalaman/kunjungan adalah objek cerita utama; Explore dibuka pada tab Visit terbaru.
2. **Dua area terpisah:** pengunjung publik tidak butuh akun; admin masuk melalui rute yang terproteksi.
3. **Explore adalah mesin discovery bersama:** city/category/food/**cuisine**/style/ingredient/allergen/rekomendasi memakai logika filter yang sama, bukan copy query per halaman.
4. **URL adalah state:** tab, search, filter, sort, grid/list, dan pagination punya URL yang bisa dibagikan dan digunakan Back/Forward.
5. **Hanya published:** semua konten/aggregate/metatag publik disaring berdasarkan published visits; draft/private hanya melalui area admin.
6. **Nama tabel singular** tidak memaksa URL plural; rute pada draft ini menggunakan segment singular demi konsistensi.

## 2. Sitemap publik — struktur yang disarankan

```text
/                                 Home — Memory Archive [P0]
├── /explore                       Explore [P0]
│   ├── ?tab=visit                 Kunjungan terbaru (default) [P0]
│   ├── ?tab=food                  Hidangan lintas kunjungan [P0]
│   └── ?tab=place                 Tempat yang punya visit published [P0]
├── /visit/{slug}                  Visit Detail [P0]
├── /place/{slug}                  Place Detail [P0]
├── /city/{slug}                   City Discovery [P1]
├── /category/{slug}               Category Discovery [P1]
├── /food/{slug}                   Food Type Discovery [P1]
├── /cuisine/{slug}                Cuisine/Tradisi Kuliner Discovery [P1]
├── /style/{slug}                  Cooking Style Discovery [P1]
├── /recommendation/{slug}         Recommendation Label Discovery [P1]
├── /about                         Tentang jurnal / penulis [P1, opsional]
├── /sitemap.xml                   Sitemap mesin pencari [P0]
└── /robots.txt                    Crawl guidance [P0]

Error views (bukan halaman navigasi biasa): 403 / 404 / 419 / 429 / 500 / 503 [P0]. Semuanya memakai komposisi Editorial Bold dan kode status dinamis; rujuk `design-system-specification.md` §1. Tindakan pemulihan harus sesuai konteks, tanpa dua tautan setara.
```

**P0 shortcut discovery** tidak membutuhkan halaman P1: tautan “Semarang”, “Mie Ayam”, “Grilled”, atau “Must Try” mengarah ke `/explore` dengan filter yang sesuai sampai landing khusus P1 tersedia.

### Rute publik, fungsi, dan data minimum

| Path | Nama route usulan | Tujuan / isi utama | Prioritas | Indexability |
|---|---|---|---|---|
| `/` | `home` | Memory Archive, kunjungan terbaru editorial, personal picks, akses Explore | P0 | Index |
| `/explore` | `explore.index` | Hasil 3 tab, keyword/filter/sort, grid/list, pagination | P0 | Index **hanya versi canonical** |
| `/visit/{slug}` | `visit.show` | Tanggal, place, cerita/review, rating, item, biaya, galeri, kesimpulan | P0 | Index bila published |
| `/place/{slug}` | `place.show` | Identitas tempat, lokasi, link Maps, agregat rating, jumlah rated visit, riwayat visit | P0 | Index bila eligible |
| `/city/{slug}` | `city.show` | Cerita/hasil curated per kota; hasil dari published visit | P1 | Index bila cukup konten |
| `/category/{slug}` | `category.show` | Kategori hierarkis, turunan, item dan kunjungan terkait | P1 | Index bila cukup konten |
| `/food/{slug}` | `food.show` | Food type standar lintas tempat; item, harga snapshot, visit terkait | P1 | Index bila cukup konten |
| `/cuisine/{slug}` | `cuisine.show` | Tradisi kuliner/regional; berbasis item published, tidak mengasumsikan place berkebangsaan tertentu | P1 | Index bila cukup konten |
| `/style/{slug}` | `style.show` | Teknik masak multi-tag; item/visit relevan | P1 | Index bila cukup konten |
| `/recommendation/{slug}` | `recommendation.show` | Kunjungan berlabel editorial, tak terkait keharusan skor tertentu | P1 | Index bila cukup konten |
| `/about` | `about` | Penjelasan siapa penulis dan bagaimana rating diberikan | P1 | Index |
| `/sitemap.xml` | `sitemap` | Hanya URL publik eligible; bukan daftar admin/private | P0 | Crawl |

**Catatan:** “index bila cukup konten” artinya tidak perlu membangun halaman kosong hanya demi SEO; detail kelayakan index ditentukan pada implementasi SEO. Unpublished selalu tak eligible.

## 3. Navigasi publik

### Global header — mobile-first

- **Brand/logo → Home**.
- **Jelajah → `/explore`** sebagai navigasi utama.
- **Kota** dan **Rekomendasi** bisa menjadi shortcut ke section Home atau Explore terfilter. Hindari membuat menu utama terlalu padat hanya karena semua jenis taxonomy tersedia.
- **Search** dapat membuka `/explore?q=...`, tanpa search backend terpisah.
- Navigasi tidak perlu menampilkan setiap taxonomy sebagai menu. Cuisine/style/ingredient adalah filter/shortcut discovery, bukan menu global baru.
- Tidak ada tombol **Login/Register** publik. Login admin tidak perlu dipromosikan di header publik.

### Footer

- Link Home, Jelajah, Tentang (jika dibuat).
- Penjelasan singkat bahwa rating adalah opini satu penulis, bukan rating komunitas.
- Tautan eksternal jika benar-benar tersedia; jangan isi placeholder kontak/media sosial palsu.

### Breadcrumbs

- Home › Jelajah › Kunjungan › [judul visit] (bisa disederhanakan menjadi Home › Kunjungan › Judul).
- Home › Tempat › [nama place].
- Home › Jelajah › Kota/Kategori/Jenis/Gaya › [nama] pada P1.
- Breadcrumb bersifat navigasi dan schema SEO bila eligible; tidak mengharuskan halaman index tambahan untuk setiap segment.

## 4. Isi dan CTA tiap page publik

### 4.1 Home `/`

**Urutan konten yang dianjurkan:** Memory Archive → kunjungan terbaru editorial → personal recommendations. Jalur kota/makanan/teknik berpindah ke Curiosity Index di Explore.

- Primary CTA: **Jelajahi Pengalaman** (`/explore`).
- Feature cards langsung ke `visit.show` atau place yang relevan.
- Visit Card F Refined memakai satu overlay link ke `visit.show`; chip taksonomi dan rekomendasi adalah link Explore terpisah. Home, Explore Visit, riwayat Place, dan kunjungan terkait memakai komponen yang sama. Visit grid dua kolom hanya pada layar lebar (>=1180px), lalu satu kolom; kartu menjadi foto-di-atas di bawah lebar kartu 700px. Food/Place grid tetap mengikuti aturan masing-masing.
- Jika konten published kosong: empty state editorial ramah, tidak menampilkan “error” dan tidak membocorkan draft.
- Pilihan featured harus punya fallback otomatis jika belum dikurasi admin; jangan membuat dependensi CMS tambahan tanpa kebutuhan.

### 4.2 Explore `/explore`

- **Default tab:** `Visit` diurutkan dari `visited_at` terbaru, tie-breaker ID untuk konsistensi.
- **Tab:** `Visit`, `Food`, `Place`; keyboard accessible.
- **Mode:** grid / list, tetap memakai URL.
- **Search:** cocokkan teks relevan, dengan query aman dan debounced bila request asinkron digunakan.
- **Filter:** kota, category, food_type, **cuisine**, **cooking style**, ingredient, **known-exposure allergen exclusion**, harga, rating, tag, label rekomendasi. Ketersediaannya tergantung tab dan data published.
- **Progressive disclosure:** search + tab + sort/grid-list + tombol Filter selalu ditemukan, 3–4 quick filters relevan, panel advanced dikelompokkan sebagai Lokasi / Makanan / Asal kuliner / Teknik memasak / Harga & rating / Bahan & alergen / Rekomendasi. Applied-filter chips dan reset tersedia.
- **Alergen:** label UI 'Sembunyikan yang **tercatat** mengandung ...' (exclude contains & may_contain); unknown **tetap mungkin muncul dengan label** dan peringatan bahwa hasil bukan jaminan aman; jangan pernah menamai mode ini allergy-safe.
- **Mobile filter sheet:** membuka panel aksesibel focus-trap/restore, collapsible groups, sticky `Reset` + `Tampilkan N hasil`, commit state ke URL setelah Apply. Desktop auto-apply boleh jika UX konsisten.
- **Sorting:** sesuai konteks, bukan satu dropdown yang menawarkan opsi tak masuk akal.
- **Empty state:** “Belum ada hasil sesuai filter” + **Reset filter**; pertahankan tab aktif.
- **Pagination:** query parameters dipertahankan di tautan Next/Previous.

| Tab | Unit kartu | Harga yang boleh ditampilkan | Rating | Sort awal |
|---|---|---|---|---|
| Visit | `visit` published | **Total pengeluaran visit**, bila dicatat | Overall visit | Tanggal kunjungan terbaru |
| Food | `visit_item` dari visit published | **Harga item snapshot** (per unit, dan jumlah jika perlu) | Rating rasa item | Kunjungan terbaru |
| Place | `place` eligible publik | Harga tidak wajib pada MVP; jika ada harus dinamai metrik dengan tepat | Rating place + jumlah kunjungan dinilai | Kunjungan published terbaru |

**Kontrak query URL (usulan, belum API contract final):**

```text
/explore?tab=food&q=mie&city=semarang&category=mie&food_type=mie-ayam
        &cuisine=thai&style=boiled&ingredient=chicken&avoid_allergen=peanut
        &price_min=15000&price_max=50000&rating_min=4
        &tag=pedas&recommendation=must-try&sort=newest&view=grid&page=2
```

| Parameter | Nilai | Aturan |
|---|---|---|
| `tab` | `visit`, `food`, `place` | Default `visit`, invalid → default aman |
| `q` | string keyword | Trim/normalize, panjang maksimum divalidasi |
| `city` | slug kota | Filter dari place milik published visit |
| `category` | slug kategori | Berlaku untuk item/visit terkait; hierarki perlu definisi include descendants |
| `food_type` | slug jenis spesifik | Contoh `mie-ayam`; category harus lebih luas (`mie`), bukan entitas duplikat |
| `cuisine` | slug tradisi kuliner | Match item cuisine (option include descendants saat memilih parent, secara konsisten) |
| `style` | slug cooking style | Match multi-tag item dalam published visits |
| `ingredient` | slug bahan | Match hanya bahan yang **tercatat** pada item; absence of record bukan negative evidence |
| `avoid_allergen` | slug/kumpulan slug alergen | Exclude item yang tercatat `contains/may_contain`; unknown diberi label eksplisit, **bukan food safety guarantee** |
| `price_min`, `price_max` | integer IDR | Visit: total visit; Food: harga item; Place: tidak berlaku pada MVP |
| `rating_min` | numeric 1–5 | Visit: overall; Food: rasa item; Place: agregat place; nullable dikecualikan bila filter aktif |
| `tag` | slug tag | Domain tag yang valid |
| `recommendation` | slug label | Rekomendasi editorial visit terkait |
| `sort` | konteks tab | Nilai whitelist di server (contoh di bawah) |
| `view` | `grid`, `list` | Default `grid`; hanya mempengaruhi layout |
| `page` | integer positif | Default 1; reset ke 1 saat filter/tab berubah |

**Sort per tab (usulan):**

- Visit: `newest` (visited_at desc), `oldest`, `rating_high`, `rating_low`, `price_low`, `price_high`.
- Food: `newest`, `rating_high`, `rating_low`, `price_low`, `price_high`.
- Place: `recent_visit`, `rating_high`, `rating_low`, `name_asc`.
- Item tanpa harga/rating tidak disulap menjadi 0; tempatkan `NULL` konsisten (mis. di akhir) saat sort.

**Ketika pindah tab:** pertahankan `q`, `city`, `category`, `food_type`, `cuisine`, `style`, `ingredient`, `avoid_allergen`, `tag`, `recommendation` **hanya jika relevan**; reset `page=1`, `sort` default jika tidak valid; `view` tetap. **Reset rentang harga ketika berpindah Food ↔ Visit/Place**, karena “harga item” dan “total kunjungan” berbeda; informasikan reset singkat agar tidak mengejutkan. Untuk Place, ingredient/cuisine/style filter berarti **ada minimal satu item published terkait**, bukan bahwa semua item tempat itu cocok. Satu utility query-state yang teruji di backend+frontend.

### 4.3 Visit Detail `/visit/{slug}`

- Hero foto cover (atau fallback estetis bila tidak ada gambar), judul, tanggal, nama place, badge status editorial bila ada.
- Rangkuman skor overall dan kriteria yang terisi; “Belum Dinilai” untuk kosong.
- Cerita pengalaman tempat, lalu item yang dipesan (nama snapshot, jumlah, harga item, rasa, catatan, foto terkait).
- Pelayanan, suasana, kebersihan, value, kesimpulan; **sembunyikan section kosong** tanpa menciptakan data.
- Total visit dengan label tersendiri; jangan keliru menampilkan subtotal item sebagai total.
- Galeri tunggal dari aset media visit; foto item memakai referensi media yang sama. Kartu/thumbnail memakai **optimized derivative**, klik viewer memakai **original via protected media route**, dengan loading/error/focus restore.
- CTA sekunder: **Lihat Tempat** dan **Buka Google Maps** bila URL tersedia.
- Konten rekomendasi/visit terkait harus published.

### 4.4 Place Detail `/place/{slug}`

- Identitas tempat; kota/provinsi dan alamat/Maps jika tersedia.
- Rating agregat 1 desimal, **N kunjungan dinilai**, serta daftar skor overall per kunjungan.
- Timeline/list riwayat visit published, terbaru lebih dulu.
- Jika seluruh visit published belum punya rating: tampil “Belum Dinilai”, bukan 0,0.
- Daftar item food yang pernah dicoba, jika dipakai, selalu berlabel **tanggal snapshot**, bukan daftar menu/harga terbaru.
- Jangan menampilkan daftar harga “terkini” dari item snapshot sebagai harga menu berlaku sekarang.

### 4.5 Discovery P1

- `/city/{slug}`: profil kota ringan → kunjungan, place, jenis makanan terkait; filter berujung Explore.
- `/category/{slug}`: kategori + subkategori → item/visit related; label kategori bukan nama menu.
- `/food/{slug}`: satu jenis standar → pengalaman tiap tempat, harga snapshot, rasa item.
- `/cuisine/{slug}`: tradisi/regional kuliner (Thai/Italian dll) → item published; parent/descendant konsisten; tidak mengklaim bahwa suatu place hanya memiliki satu cuisine.
- `/style/{slug}`: satu cooking style → menu yang punya tag tersebut, dengan multi-tag tetap berlaku.
- `/recommendation/{slug}`: label kurasi editorial → visit terkait dan alasan/review; **tidak** otomatis berdasarkan threshold rating.
- Semua landing memanggil query dasar yang sama dengan Explore agar jumlah hasil, filter, dan status konsisten.

## 5. Sitemap admin

```text
/admin/login                         Login admin [P0]
/admin                               Dashboard [P0, auth]
├── /admin/visit                     Visit List [P0]
│   ├── /admin/visit/create          Quick Entry (minimal fields) → Full Editor [P0]
│   ├── /admin/visit/{id}/edit       Full Editor [P0]
│   └── /admin/visit/{id}/preview    Preview terproteksi [P0]
├── /admin/place                     Place List [P0]
│   ├── /admin/place/create          Create Place [P0]
│   └── /admin/place/{id}/edit       Edit Place [P0]
├── /admin/taxonomy                  Taxonomy Hub [P0]
│   ├── /admin/taxonomy/category     Category CRUD [P0]
│   ├── /admin/taxonomy/food-type    Food Type CRUD [P0; jenis spesifik terpisah dari kategori luas]
│   ├── /admin/taxonomy/cuisine      Cuisine/Tradisi CRUD [P0]
│   ├── /admin/taxonomy/style        Cooking Style CRUD [P0]
│   ├── /admin/taxonomy/ingredient   Ingredient CRUD [P0]
│   ├── /admin/taxonomy/allergen     Controlled Allergen Listing [P0]
│   ├── /admin/taxonomy/tag          Tag CRUD [P0]
│   └── /admin/taxonomy/recommendation  Recommendation Label CRUD [P0]
├── /admin/media                     Asset maintenance [P1]
└── /admin/settings                  Site settings [P1, sesuai kebutuhan]

Action POST/PATCH/DELETE diproses pada resource handler berizin, bukan halaman navigasi tersendiri.

`GET /media/{uuid}/{variant}` adalah resource route untuk mengirim original/derivative secara **visibility-aware**, bukan halaman menu. Default private media storage, akses publik hanya untuk visit published; admin preview memakai authorized access.
```

### Rute admin, tujuan, akses

| Path | Tujuan utama | Auth / hak akses | Aksi khas |
|---|---|---|---|
| `/admin/login` | Autentikasi | Guest admin | Login + rate limit; jangan mengungkap apakah akun ada |
| `/admin` | Ringkasan konten | Admin | Draft belum lengkap, private, recently published, shortcut Tambah Visit |
| `/admin/visit` | Daftar/kelola visit | Admin | Filter status/place/tanggal, edit, preview, publish/unpublish |
| `/admin/visit/create` | Quick Entry + opsi Full Editor | Admin | Place searchable select + inline create, visited_at default Asia/Jakarta hari ini, slug auto/editable (bukan wajib input), save draft |
| `/admin/visit/{id}/edit` | Editor bertahap | Admin | Items, cuisine/style multi, bahan/alergen opsional dengan sumber, reviews, harga, optimized upload/cover, preview/publish |
| `/admin/visit/{id}/preview` | Preview aman | Admin | Rendering menyerupai publik tetapi tidak mengubah status |
| `/admin/place` | Pencarian/CRUD place | Admin | Autocomplete, potensi duplikat |
| `/admin/taxonomy/...` | Kelola vocabularies | Admin | Category/food_type/cuisine/style/ingredient/tag/recommendation searchable; allergen controlled seeds, check usage sebelum delete |
| `/admin/media` | Maintenance asset | Admin | Orphan, metadata, error upload (P1) |
| `/admin/settings` | Konfigurasi site ringan | Admin | Hanya setting yang benar-benar diperlukan (P1) |

**Aturan preview:** tidak boleh memiliki URL `/visit/{slug}?preview=1` yang bisa dilewati publik; gunakan `/admin/visit/{id}/preview` di bawah middleware/policy. Preview tidak masuk SEO/sitemap dan sebaiknya memakai `noindex`.

## 6. Alur perpindahan halaman

### Publik — menemukan hidangan dan asal tempat

```text
Home
  ↓ CTA "Jelajahi Pengalaman"
Explore (Visit terbaru)
  ├── Ganti tab Food → Food Card → Visit Detail → Place Detail
  ├── Filter kota/style → Visit Card → Visit Detail
  └── Ganti tab Place → Place Card → Place Detail → riwayat Visit
```

### Admin — menerbitkan pengalaman

```text
Admin Login → Dashboard
   ↓ Tambah Visit
Quick Entry → [Search-select / Inline-create Place] → Default visited_at now → Simpan Draft
   ↓ Lanjutkan
Full Editor (Items, Cuisine/Style, Ingredients/Allergens, Review, Photos, Price)
   ↓ Preview terproteksi
Validasi publish → Publish → Visit publik
   ↓
Home / Explore / Place aggregates / SEO diperbarui melalui invalidation tepat
```

### Kesalahan dan state

```text
Tidak ada published visit  → Empty Home/Explore, bukan 500
Filter tanpa hasil        → Empty result + Reset filter
Slug tidak dikenal        → 404 branded
Slug nonpublished publik  → 404 branded (tanpa bocorkan keberadaan)
Admin tidak login         → Redirect /admin/login
Admin tidak berizin       → 403 branded
CSRF/session expired      → 419 branded, recovery aman
Rate limit                → 429 branded
Gangguan sistem           → 500/503 branded tanpa detail internal
```

## 7. Public visibility & SEO rule per route

| Konten | Saat draft/private | Saat published | Jika kembali private |
|---|---|---|---|
| `visit.show` | 404 bagi publik | 200, metadata indexable | 404 bagi publik; keluarkan dari sitemap, invalidasi cache |
| `place.show` | 404 jika tidak punya published visit | 200 bila >=1 eligible visit | 404 bila tinggal nol eligible visit |
| Explore Food/Place | Item/tempat tidak dihitung jika hanya berasal dari visit private | Muncul dari eligible published sources | Hilang dari hasil & counter |
| Discovery pages | Tidak menampilkan hit/counter dari private | Hanya published aggregates | Recalculate/invalidate dengan benar |
| Image public | Rute media wajib menolak request publik | Original atau derivative via **visibility-aware controller** hanya untuk visit published | Request baru ditolak; cache/salinan yang sempat diunduh tidak dapat dijamin terhapus |
| OG, canonical, JSON-LD, sitemap.xml | Jangan memuat konten private | Berisi identitas published yang sesuai | Cabut konten stale |

**Robots dan canonical:**

- `/explore` utama dapat index; kombinasi filter (`?q=...`, `?city=...`, dan seterusnya) sebaiknya canonical ke `/explore` atau `noindex,follow` sesuai strategi SEO final, bukan terindeks semua.
- Landing P1 `/city`, `/food`, `/category`, `/cuisine`, `/style`, `/recommendation` adalah halaman canonical berbeda jika memiliki konten unik yang layak.
- Filter alergen/query sensitif tidak membentuk landing SEO yang menampilkan klaim aman atau index combinatorial.
- Login, admin, preview, dan rute aksi tidak ada di sitemap dan tidak boleh terindeks.
- Redirect 301 untuk slug publik yang diubah, dengan pengecekan status published saat redirect. Hindari redirect private slug ke target publik secara tak sengaja.

## 8. Komponen bersama yang dipetakan ke halaman

| Komponen reusable | Penggunaan |
|---|---|
| `SiteHeader`, `SiteFooter`, `Breadcrumb` | Seluruh area publik |
| `VisitCard`, `FoodCard`, `PlaceCard` | Home, Explore, detail terkait, discovery P1 |
| `RatingDisplay`, `RatingInput` | Public detail vs admin editor; logika server tetap sumber kebenaran |
| `PriceDisplay` | Visit total vs Food item secara eksplisit; jangan reusable secara ambigu |
| `ExploreFilterBar`, `AdvancedFilterSheet`, `FilterChip`, `TabSwitch`, `ViewToggle`, `Pagination` | Explore dan discovery P1; progressive disclosure, filter count + applied chips, URL persistence |
| `PhotoGallery`, `PhotoViewer`, `MediaPicker`, `ResponsiveImage` | Original protected viewer vs optimized navigation; Visit detail, Admin editor/preview |
| `CustomAlert`, `CustomModal`, `ToastService` | Shared design system; admin terutama, publik sesuai kebutuhan |
| `Skeleton`, `EmptyState`, `ErrorState` | Semua page sesuai state dan kebutuhan aktual |
| `AdminFormField`, `SearchableCombobox`, `InlineCreateDialog`, `EditableAutoSlug`, `StatusBadge`, `PublishAction` | Admin smart defaults, edit manual slug, loading/validation, server Form Request + Policy |

### Shared templates dan anti-AI-slop contract

- Header, footer, admin shell, breadcrumbs, alert/modal/toast, card dan filter panel adalah **Blade layout/components atau `@include` partial**; tidak disalin per halaman. Layout memakai satu pola konsisten (`<x-layout...>` slots **atau** `@extends`+`@yield`).
- CSS semantic tokens → primitives → domain → pages, JS modular melalui Vite. Tidak ada inline scripts berulang, listener duplikat, `window.alert/confirm`, atau god component.
- Spasi dan hirarki judul memisahkan section; **jangan** garis panjang sebagai dekorasi default, random gradients, fluff, kartu kosong, atau button sama level yang berbeda ukuran hanya akibat panjang teks. Variasi editorial hanya jika responsif dan accessible.
- Wajib uji tombol sekelas di satu action-group dengan tinggi/min-width konsisten; mobile boleh full-width. Alert/Modal/Toast accessible; foto performant dengan LCP prioritized dan derivative di listing.

## 9. Perilaku URL, akses, dan teknis penting

- Permintaan `/explore` tanpa `tab` harus **setara** dengan `tab=visit&sort=newest`, tanpa harus memaksa redirect yang tidak perlu.
- Semua parameter disaring terhadap whitelist; nilai invalid tidak masuk langsung ke dynamic SQL `ORDER BY`.
- Paginasi stabil: timestamp + unique tie-breaker ID; no N+1 pada item/galeri/place.
- Jika sort harga/rating dan field `NULL`, penempatan tetap deterministik, tidak berubah tiap reload.
- Filter kota/type/style/recommendation menggunakan relasi published visits, bukan keberadaan data master taxonomy saja.
- Untuk UI accessible, tab menggunakan semantics yang sesuai dan focus keyboard; filter panel mobile memiliki label yang jelas, bisa di-close, dan mengingat state URL.
- Semua external Google Maps URL divalidasi/di-sanitize; buka sebagai navigasi eksternal yang jelas.
- Satu source of truth untuk slug generation dan route binding; **auto generate, editable dan manual override lock**, collision suffix, redirect jika berubah saat published. Gunakan 404 untuk visit private tanpa mengungkap status.
- Search-select place/taxonomy dengan keyboard+ARIA dan inline-create, default visited_at lokal saat create, status draft, quantity 1; tidak default rating/harga/bebas alergen.
- Alat filter alergi mengecualikan **known** contains/may_contain saja dan secara jelas menandai unknown (bukan jaminan aman).
- Rute admin write memakai `POST/PATCH/DELETE` + CSRF + server authorization; double submit dicegah di UI dan dilindungi secara transaksi jika risikonya nyata.

## 10. Minimum QA checklist khusus sitemap

- [ ] Semua link utama Home → Explore → Visit → Place tidak dead-end; mobile navigation dan breadcrumb konsisten.
- [ ] Explore default newest visit; switching tiga tab mempertahankan keyword dan filter yang kompatibel; `sort` tidak valid direset; Back/Forward bekerja.
- [ ] Filter harga tab Food memilih harga item, Visit memilih total, Place tidak menampilkan filter ambigu.
- [ ] Filter rating Food menggunakan item taste, Visit overall, Place published-visit aggregate.
- [ ] Cuisine vs cooking style dibedakan pada data/label/filter; item mendukung multiple cooking style dan cuisine item-level.
- [ ] Ingredient dan allergen evidence publik hanya dari published item. `avoid_allergen` exclude contains/may contain, unknown ditandai dan disclaimer hadir.
- [ ] Advanced filter desktop/mobile usable dengan keyboard, group collapsible, active chips, reset, apply mobile dan URL shareable.
- [ ] Header/footer/layout reusable Blade; anti-AI-slop styling, button equal-level parity dalam group.
- [ ] Draft/private tidak muncul pada card, jumlah hasil, rekomendasi, detail, foto, metadata, dan sitemap.
- [ ] Place dengan 0 published visit tidak bisa dibuka publik sesuai keputusan U1.
- [ ] Route preview hanya bisa dibuka admin; noindex; screenshot/OG publik tidak mengakses foto private.
- [ ] Navigasi image optimized, viewer original; unpublish segera menolak request media baru; retry derivative aman dan tidak menghapus original.
- [ ] Error 403/404/419/429/500/503 memiliki tampilan branded dan recovery yang sesuai.
- [ ] Landing P1, bila dibuat, memperoleh hasil dari query dasar yang sama dengan Explore.
- [ ] Direct navigation ke URL query panjang, refresh, pagination, serta browser history tidak kehilangan state.

## 11. Keputusan sebelum membuat Laravel routes

1. Setujui `category` sebagai kelompok luas dan `food_type` sebagai jenis spesifik sehingga `/food/{slug}` tidak redundan dengan `/category/{slug}`.
2. Putuskan apakah `city` adalah master data tersendiri atau sekadar nilai terstandardisasi pada place. Jangan membangun entitas kota berat tanpa kebutuhan.
3. Tentukan kebijakan slug untuk place dengan nama sama di kota berbeda; sebaiknya unique global slug dengan disambiguasi yang mudah dipahami.
4. Tetapkan scope filter lintas tab, terutama kategori dan rekomendasi, serta policy canonical untuk query kombinasi.
5. Setujui apakah `/about` dan landing khusus P1 diperlukan sebelum launch; MVP tetap tidak bergantung pada keduanya.
6. Setujui `cuisine` hierarkis/multi item-level sebagai tradisi kuliner dan `cooking_style` sebagai teknik; definisikan label `Western` editorial.
7. Setujui filter ingredient/alergen informational di P0 dengan disclaimers/unknown dan tanpa klaim safe; tetapkan seed & source dalam ERD.
8. Setujui media protected route + optimized variants, auto slug editable, default visited_at lokal, dan advanced filter UX dari design system + wireframe.
9. Setelah langkah #1–#4 disetujui, susun instruksi Codex langkah #5; Codex wajib inspeksi repo dan merumuskan migration/test sesuai repo yang nyata.

---

**Implikasi untuk Codex:** gunakan sitemap sebagai rujukan rute dan hierarki layar, tetapi **inspeksi repo terlebih dahulu** sebelum menambahkan/mengubah routes, blade components, CSS/JS, dan tests. Jangan menghancurkan fitur existing atau mengklaim route sudah dibuat dari dokumen desain saja.
