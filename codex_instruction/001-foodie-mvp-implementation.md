# CODEX INSTRUCTIONS — FOODIE MVP END-TO-END IMPLEMENTATION

> **Instruction ID:** `001-foodie-mvp-implementation`  
> **Target:** Repository Foodie yang sedang aktif di workspace Codex  
> **Status:** Instruksi eksekusi — bukan laporan bahwa fitur sudah dibuat  
> **Bahasa UI:** Indonesia · **Timezone:** Asia/Jakarta · **Mata uang:** IDR  
> **Stack target:** Laravel + MySQL + Blade + Vite; Alpine.js/Livewire hanya bila memang memberikan manfaat nyata  
> **Definition of success:** Admin dapat mencatat dan mempublikasikan kunjungan secara praktis; publik dapat menelusuri Visit/Food/Place dengan benar; histori, privasi, media, keamanan, dan desain konsisten serta diuji.

## A. Misi dan cara bekerja

Bertindak sebagai **senior Laravel architect, engineer, database designer, UI/UX designer, accessibility specialist, security reviewer, dan QA lead**. **Implementasikan seluruh scope P0 Foodie**, bukan hanya membuat rencana, komponen kosong, route placeholder, atau screenshot desain. Kerjakan secara **bertahap dan terverifikasi**, dengan keputusan desain yang proporsional terhadap ukuran proyek.

### Lima dokumen authoritative tersedia di ROOT project

Baca seluruh dokumen ini **sebelum mengubah kode**. Gunakan nama file persis berikut dan **jangan** mengharuskan pemilik memindahkannya ke folder `docs/`:

1. `product-blueprint.md` — tujuan produk, domain, aturan bisnis, P0/P1/P2, acceptance criteria.
2. `sitemap.md` — rute, navigasi, state URL Explore, cakupan publik/admin, SEO.
3. `erd-database-specification.md` — data model, naming singular, FK/pivot, rating/harga, media, bahan/alergen.
4. `design-system-specification.md` — visual identity, tokens, komponen, accessibility, anti-AI-slop, filter UX.
5. `user-flow-wireframe.md` — user journey, wireframe desktop/mobile, interaksi dan UAT.

**Urutan penyelesaian kontradiksi:** keselamatan/authorization/data integrity terlebih dahulu; kemudian Product Blueprint untuk business scope, ERD untuk domain/storage, Sitemap untuk route/filter, Design System untuk token/interaksi, Wireframe untuk alur/layout. Jangan diam-diam mengganti aturan bisnis; catat keputusan di `docs/implementation-decisions.md`. Jika ada keputusan terbuka, gunakan default yang tertulis pada dokumen v1.1 dan solusi P0 paling sederhana yang aman. Hanya minta keputusan user bila benar-benar berisiko kehilangan data, butuh layanan berbayar, atau ada kontradiksi fatal yang tidak bisa diselesaikan secara aman.

**Prinsip:** KISS, DRY, YAGNI; pakai framework conventions kecuali nomenklatur tabel; tidak ada SPA/dependency berat tanpa alasan yang terukur. Hindari `god service`, banyak layer abstraksi untuk operasi satu langkah, CSS/JS copy-paste, logic bisnis dalam Blade, atau query database pada view. Tetap perhatikan usability sungguhan dan visual yang khas, bukan template AI generik.

### Operasional dan keamanan repository

- Kerjakan **dalam repository yang benar-benar dibuka oleh Codex**. Jangan menganggap path Windows tertentu benar tanpa mengecek workspace.
- **Wajib inspeksi dulu:** `git status`, tree/directory, README, `composer.json`, `package.json`, `.env.example` (jangan cetak secrets dari `.env`), Laravel version, PHP version, DB connection, route, migration, model, middleware/auth, Blade layout, assets, tests, storage/queue, existing build scripts.
- Cek apakah repository kosong, scaffold Laravel, atau punya fitur existing. **Pertahankan fitur dan data yang ada**; perluas implementasi secara minimal. Jangan overwrite file custom hanya karena struktur dokumen memberi contoh path berbeda.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP`, menghapus data/media, `git reset --hard`, `git clean`, atau destructive cleanup terhadap database existing.
- Gunakan DB **testing terisolasi** untuk tes destructive, fixtures, dan restore. Jika koneksi DB tidak aman/tersedia, jangan membuat klaim tes lulus; catat blocker dan jalankan tes yang mungkin.
- Jangan membuat credential production, mempublikasikan `.env`, membuat akun admin dengan password hardcoded, atau membuka registration publik. Setup admin pertama harus aman (documented CLI/seed yang membaca input/env secara aman, tidak hardcode secret).
- Bila ada dependency baru, justifikasi secara singkat (mengapa native Laravel/Blade/Alpine tidak cukup); utamakan paket OSS gratis, maintenance aktif, lisensi sesuai, dan lockfile konsisten.
- Jangan mencoba menyelesaikan semua fitur sekaligus dalam satu perubahan besar tak terverifikasi. Implementasi satu fase lalu jalankan tests/lint/build yang relevan, perbaiki, baru lanjut. **Teruskan sampai semua P0 selesai jika lingkungan memungkinkan**; jangan berhenti hanya karena roadmap selesai ditulis.
- Jangan membuat commit otomatis jika workflow repo tidak mengharuskannya. Catat file yang berubah; jangan menghapus perubahan user yang sudah ada.

## B. Scope resmi & keputusan default untuk eksekusi

### P0 — wajib benar-benar berfungsi

1. Authentication/authorization **hanya admin**; tanpa public registration.
2. Admin dashboard yang berguna; CRUD place, visit, visit item, taxonomy, media; quick entry & full editor; draft/private/published; preview/publish/unpublish.
3. Domain lengkap: `place`, `visit`, `visit_item`, `category`, `food_type`, `cuisine`, `cooking_style`, `ingredient`, `allergen` + evidence, `tag`, `recommendation_label`, media original/variant, pivots; historical snapshots.
4. Form efisien: searchable place/taxonomy; create-new inline yang aman; auto slug editable; default visited_at Asia/Jakarta; quantity default 1; sensible validation; error/recovery/anti-double-submit.
5. Foto high-quality original **private** + optimized variants untuk card/hero/thumbnail; viewer meminta original dengan authorization; cover dan relasi item tidak menggandakan file; retry/error lifecycle.
6. Shared reusable Blade layouts/header/footer/breadcrumb, semantic design tokens, UI primitives dan domain components, `CustomAlert`, `CustomModal`, `ToastService`, accessible combobox, loading/empty/error states, page 403/404/419/429/500/503.
7. Home editorial; Explore dengan tab Visit/Food/Place; visit detail; place detail; filter dasar **dan lanjutan** termasuk cuisine/style/ingredient/known-allergen-exclusion; sort; grid/list; URL state; pagination.
8. Status enforcement pada **setiap** entry point publik termasuk image, suggestions, counts, SEO, dan aggregate. Structured rating, separate price semantics, recommendations editorial.
9. Metadata/canonical/OG, `sitemap.xml`, `robots.txt` tanpa private; backup/restore DB+media terdokumentasi dan diuji dalam lingkungan aman sejauh memungkinkan.
10. Automated tests + UAT evidence di `docs/implementation-report.md` dengan hasil aktual, bukan sekadar checklist tercentang.

### Bukan P0 — jangan memperluas ruang lingkup

- Halaman editorial terdedikasi `/city/{slug}`, `/food/{slug}`, `/category/{slug}`, `/cuisine/{slug}`, `/style/{slug}`, `/recommendation/{slug}` **P1**. Pada MVP, arahkan discovery shortcut ke `/explore?...` yang memakai engine filter sama.
- `/about` opsional P1; admin media-maintenance global dan settings luas P1; public REST API P2; public account/comment/review/donation/booking/delivery/payments di luar produk.
- Strict **“verified allergen free / aman alergi”** tidak tersedia; jangan sertifikasi makanan dari daftar ingredients yang belum tentu lengkap.
- Jangan membuat data analitik dashboard dekoratif, CMS umum, integrasi Google Maps berbayar, AI food classifier, atau full image processing infrastructure yang memerlukan layanan berbayar.

### Domain decisions — jadikan baseline implementasi

- `visit` adalah catatan pengalaman; `place` hanya eligible publik jika punya ≥1 published visit. Draft/private sama sekali tidak dapat diakses publik.
- `category` = kelompok umum hierarkis; `food_type` = jenis spesifik; **jangan** duplikasi nama/arti di kedua entitas. Nama menu adalah snapshot `visit_item.menu_name`, bukan product/menu catalog global.
- `cuisine` = tradisi kuliner regional/item-level, parent pendek, multiselect jika fusion; `Western` adalah editorial term ambigu, jangan infer Italian → Western. `cooking_style` = teknik memasak item-level multi-select, berbeda domain.
- `ingredient` = bahan yang diketahui/dicatat, bukan resep terjamin lengkap. `allergen` = evidence status `contains`, `may_contain`, atau **unknown karena record tidak ada**. Sumber informasi dapat dicatat. Bukan diagnosis/sertifikasi.
- Label rekomendasi melekat ke visit, **tidak** otomatis ditentukan oleh rating.
- `private` adalah status terpisah dari draft; rating dan biaya boleh null dan tidak menjadi syarat publish bila tidak relevan. Publish tetap butuh identitas kunjungan yang memadai menurut gate.
- Gambar original dan semua variants disimpan pada disk terproteksi; viewer via controller yang mengecek status *saat request*. Optimasi original hanya konservatif tanpa menurunkan dimensi/kualitas signifikan; derivative untuk navigasi.
- All **MySQL table names singular** tanpa pengecualian; nama file, route, namespace Laravel boleh konvensional sesuai praktik yang sudah ada. Eloquent model perlu deklarasi `$table` dan auth provider dikonfigurasi agar tidak mengasumsikan `users`.

## C. Phase 0 — Audit & implementation mapping (wajib pertama)

1. Baca kelima dokumen utuh, identifikasi P0, open decisions, tabel, route, UI components, acceptance/UAT scenarios.
2. Lakukan audit existing implementation; temukan route/model/schema/asset yang sudah bisa dipakai. Tandai area existing yang harus dilindungi, vendor dependencies, dan test baseline.
3. Buat `docs/implementation-decisions.md` berisi:
   - kondisi repository nyata (versi framework, status DB/auth, existing views/test),
   - keputusan default U1–U10 / D1–D7 yang benar-benar dipakai,
   - selisih desain vs existing dan bagaimana menyelesaikannya tanpa data loss,
   - daftar fase dan dependency, risiko kritis, P1 yang **tidak** dikerjakan.
4. Jika kondisi aman, jalankan baseline `composer test` / `php artisan test` / `npm run build` (hanya jika terpasang/tersedia), rekam **hasil sebenarnya**. Jika gagal sebelum perubahan, dokumentasikan dengan jelas.
5. Susun/cek P0 traceability list `docs/implementation-checklist.md`: feature → files → tests → status. Ini alat kerja internal, bukan pengganti implementasi.

**Gate Phase 0:** bisa menunjukkan kondisi repo dan rencana perubahan konkrit, tanpa menebak struktur project atau mengorbankan pekerjaan existing.

## D. Phase 1 — Laravel foundation, design tokens, shared component shell

- Setup/config Laravel & Vite sesuai versi nyata; `APP_TIMEZONE=Asia/Jakarta` atau konfigurasi waktu yang sesuai Laravel; locale Indonesia dan format IDR konsisten. Jangan merusak ketepatan instant UTC/database: buat explicit mapping antara input waktu lokal dan penyimpanan/penampilan, tes batas tengah malam.
- Bangun **semantic token layer** sesuai `design-system-specification.md`: raw palette terpisah dari semantic colors, typography scale, spacing, layout, radius, shadow, focus, control sizes, z-index, motion. Nilai proposal dari dokumen perlu diperiksa kontras nyata; jangan hardcode per halaman.
- Bangun shared public/admin shell dengan satu konvensi layout yang konsisten (Blade component slots **atau** `@extends/@section/@yield`). Reuse header/footer/breadcrumb, skip link, main landmark, page title/meta. `@include` untuk partial sederhana. Formulir dan navigasi bukan copy-paste per view.
- Implementasikan primitives: Button (variants + sizes/loading/disabled), FormField, Input/Select/Combobox, Badge/Chip, Tabs, Rating Input/Display, Price Display, Empty/Error/Skeleton states, Cards, Alert, Modal/Dialog, Toast container/service; buat domain components ketika page membutuhkan.
- **Modal foundation**: focus trap/restore, Escape close, backdrop/scroll lock, ARIA name/description, destructive confirmation. **Toast**: stack, dismiss, timeout/pause accessible live announcements; kritis error harus tetap inline Alert. Hindari native `alert/confirm` browser.
- CSS modular `tokens → primitives → domain → pages`; JS ES modules via Vite, no global fragile selectors/listeners and no duplicated inline handlers. Alpine.js boleh digunakan secara hemat untuk dropdown/modal/accordion; Livewire hanya bila memberi ROI riil dan tidak bertabrakan dengan stack existing.
- Design anti-AI-slop: editorial dark/cinematic dengan foto dominan, whitespace dan hierarki; **bukan** divider horizontal di setiap section, gradient acak, fake badges, repetitive CTA, faux dashboards, atau flavor text tanpa fungsi. Semua button **selevel pada action group** harus sama tinggi/min-width yang konsisten; gunakan component API, bukan nilai pixel per caption.
- A11y: WCAG 2.2 AA target, 4.5:1 normal text / 3:1 important UI, focus ring visible, keyboard, touch min 44px target internal, reflow/zoom 200%, `prefers-reduced-motion`; jangan menyamakan 44px dengan ambang wajib WCAG.

**Gate Phase 1:** Vite assets build; halaman shell dapat dirender; komponen critical punya state demo atau tests; tidak ada duplikasi header/footer dan tidak ada native confirm/alert.

## E. Phase 2 — Database domain, constraints, models, calculations, authorization

### Data modeling

Implementasikan migration+models/relations/seed/factories berdasarkan diagram dan tabel **singular** di `erd-database-specification.md`. Minimal tabel: `user` (atau safe migration strategy jika repo existing sudah memakai plural), `place`, `visit`, `visit_item`, `category`, `food_type`, `cuisine`, `cooking_style`, `ingredient`, `allergen`, `tag`, `recommendation_label`, `media`, `media_variant`, `slug_redirect`, dan seluruh pivots yang diperlukan (`visit_item_cuisine`, `visit_item_cooking_style`, `visit_item_ingredient`, `visit_item_allergen`, `ingredient_allergen`, `visit_item_media`, `visit_tag`, `visit_recommendation_label`). Jangan membuat tabel plural. Jika skema existing berbeda, migrasikan secara **nondestruktif**, dengan backup & alasan tertulis; hindari pura-pura aman hanya dengan rename langsung.

- FK benar, `onDelete` terencana, unique slug/code/composite pivot, proper nullable, timestamp, owner/visit scope, index query yang dibutuhkan. Hindari `cascade delete` tak disengaja pada jejak kunjungan dan original file.
- `visit.cover_media_id` nullable dan harus menunjuk media **milik visit yang sama**. `visit_item_media` hanya boleh menghubungkan item dan media yang punya `visit_id` sama, enforced di action/backend (opsional tambah constraint DB yang feasible). Uji cross-visit attempts.
- `visit_item.unit_price_idr` dan `visit.total_spent_idr` integer nonnegatif, **terpisah**; `NULL != 0`, tidak auto-derive total dari subtotal item.
- Input rating adalah 1.0–5.0 kelipatan 0.5, `NULL` sah. Gunakan decimal/validated numeric, bukan float yang dibulatkan prematur.
- `taste(visit) = average(non-null taste_rating item)`; `overall(visit) = simple average(non-null [taste, service, atmosphere, cleanliness, value])`; `place rating = average(overall rated published visits)`; rated visit count bukan reviewer count. Jangan bulatkan intermediate; tampilkan agregat **1 desimal**, test misalnya taste 4.0 + service 3.5 = overall 3.75 → 3.8. `NULL` keseluruhan → “Belum Dinilai”.
- Model centralized `publishedVisible()` eligibility scope (`published` + nondeleted visit/place); semua public query reuse source yang sama, dengan eager loading dan stable pagination. Jangan menyebar cek status yang berbeda ke setiap controller.
- Gunakan Form Request, Policy/Gates, validation/status transitions, services/actions **hanya jika domain logic perlu**. Transaction untuk item/pivot updates, status transition, cover assignment, slug redirect; DB transactions tidak membungkus file IO berlarut.
- `slug` otomatis dari name/title dengan fallback visit place+date+suffix, unique collision-safe; manual override respected. Edit nama existing tidak mengubah slug tanpa aksi sadar; rename published memerlukan redirect 301 yang diotorisasi dan tetap check published eligibility. Jadikan satu generator/service reusable sesuai scope.
- Seeder allergen terkontrol berdasarkan daftar FDA9/EU14 dan perbedaan wheat vs kelompok gluten-containing cereals sebagaimana tercatat di ERD §8; jangan mengartikan mapping otomatis sebagai label makanan benar. Seed idempotent, tidak overwrite editorial data tanpa alasan, dan sertakan keterangan asal referensi.
- Buat factory & dataset fixture realistis untuk zero, one, many visits; null ratings/prices; mixed statuses; media; multi cuisines/styles; contains/may_contain/unknown; duplicate names/slugs; many items dan panjang teks.

### Auth & security

- Tutup public registration dan jangan expose login admin di public navbar. `GET /admin/login`, login rate limit/session/CSRF, authorized `/admin/*`; logout aman; policy untuk CRUD, preview, upload, export jika ada; session cookie config sesuai env. Jangan menganggap hidden button = authorization.
- Semua taxonomy mutation dan cross-owner references diberi authorization; dynamic order-by only whitelist; validasi external URL `http(s)` khusus lokasi; escape semua user-generated content pada Blade.

**Gate Phase 2:** migrate isolated DB berhasil; factories/seeds idempotent; unit/feature tests rating, price, slug, status visibility, relationship constraints lulus; tidak ada tabel plural baru dan tidak bocor draft/private.

## F. Phase 3 — Admin information architecture, input praktis, CRUD & status flow

### Admin navigation & dashboard

- `/admin`: satu CTA dominan **Tambah Kunjungan**, status ringkas relevan, draft butuh kelengkapan, media failed processing jika ada, published terbaru; jangan menambah KPI kosong atau cards dekoratif.
- `/admin/place`: list, search, create/edit; nama wajib untuk place, alamat/kota/provinsi/maps/koordinat optional; dukung warung/pedagang kecil. Deteksi kandidat duplikat dengan normalized name+area, jangan merge otomatis.
- `/admin/taxonomy`: searchable management `category`, `food_type`, `cuisine`, `cooking_style`, `ingredient`, `tag`, `recommendation_label`; controlled allergen list/evidence admin dengan operation aman. Ancestor cycles dan delete referenced terms harus ditolak atau dibuat inactive.
- CRUD taxonomy dari editor boleh inline-create hanya pada data aman, lalu pilih hasil baru **tanpa kehilangan state** pada form utama. Untuk controlled allergen jangan otomatis membuat istilah sembarangan.

### Quick Entry & Full Editor

- `/admin/visit` list dengan status/place/date filters, edit/preview/publish/unpublish; selected status consistent.
- `/admin/visit/create` **Quick Entry**: searchable place combobox (recent, async search, no match, create inline), `visited_at` prefilled **Asia/Jakarta now** hanya untuk create, catatan singkat optional, status draft. Slug tidak perlu diisi. Simpan draft **tanpa wajib item/rating/price/photo**; lanjutkan ke editor pada row yang sama.
- `/admin/visit/{id}/edit` Full Editor: **Dasar → Hidangan → Ulasan → Foto → Pratinjau & Publish**, dapat menggunakan sections/stepper/accordion sesuai stack dan responsif. Shared components antara Quick/Full, tidak menduplikasi Form Request dan validation logic.
- Item repeater: item name snapshot, optional food type; quantity default UI 1; unit price nullable; taste nullable; cuisine multiselect; cooking style multiselect; ingredient known multiselect + evidence; allergen evidence optional with status/source; notes; photo linking. Tidak mengirim setiap perubahan ke server secara membabi buta; saved state jelas.
- Review: pengalaman tempat, pelayanan, suasana, kebersihan, value for money, kesimpulan; kosong tidak tampil sebagai 0/bagian kosong. Preview render visual publik yang terproteksi.
- Slug UI: auto preview dengan disclosure **Edit slug**; manual edit tidak overwritten by typeahead/title; unique/collision error actionable; ketika published slug diganti, konsekuensi redirect jelas dan diproses atomik.
- Hanya default hal yang bukan fakta: visited_at lokal saat create, status draft, quantity 1, slug, sort order. **Jangan** default rating 5, harga 0, cuisine/style dari tebakan, allergen-free, ingredient lengkap, atau alamat palsu.
- Publish gate validasi minimum: place valid, visited_at/identitas kunjungan terisi, ada narasi/isi layak tampil sesuai dokumen; media derivative/fallback tidak rusak; status private/draft tetap opsi sadar. Jangan memaksa semua review rating/foto/harga lengkap. Jika gagal: error summary + inline validation, focus first error, **tetap draft**.
- Inline Alert untuk failure actionable; Toast untuk success singkat; disabled/loading/unsaved changes/anti-double-submit (UI dan backend idempotency/race strategy proportional). Form gagal server tidak kehilangan input; partial item state restored; duplicate submission tidak membuat dua visit.
- Semantik keyboard combobox (ARIA, Esc, arrows, Enter/Tab, focus, loading/no-results), accessible modal inline-create, correct click/touch behavior. Mobile editor nyaman tanpa horizontal scroll.

**Gate Phase 3:** Admin dapat login → create inline place → save minimal draft → tambah multi item/taxonomy → rating/review → preview → private/publish → unpublish → edit dengan state aman. Jalankan feature tests dari journey tersebut.

## G. Phase 4 — Media storage, derivatives, gallery, authorization

- Original disimpan private dengan UUID acak, file MIME/sniffing + extension check, max bytes/dimensions, upload limits, image decoding safe, EXIF GPS removed dari output publik. Jangan biarkan SVG aktif atau payload executable jika tidak divalidasi/sanitasi secara kuat; whitelist MIME rasional.
- Jika mampu optimasi original secara konservatif (metadata cleanup / lossless), temp file + checksum/verify before replace; **jangan downscale original** atau menghapus karena codec gagal. Derive thumbnail/card/hero/large hanya sesuai kebutuhan tampilan; quality/format native WebP fallback bila encoder tersedia. Gunakan library server yang memang tersedia, bukan berasumsi Imagick/AVIF installed.
- Metadata `media` + `media_variant` dengan processing states `pending/processing/ready/failed`, retry idempotent, cleanup safely; queue integration jika queue worker benar tersedia, jika tidak gunakan controlled sync/database queue dengan instruksi worker yang jelas dan tanpa stuck pending.
- `GET /media/{uuid}/{variant}` termasuk `original`: check auth admin **atau** current published visit owner; unknown/private 404/403 tanpa existence leak; never expose disk raw URLs atau storage symlink untuk sumber privat. Pastikan `Cache-Control` strategy transparan: prioritas revoke pada request berikutnya, jangan menjanjikan menarik kembali cache/file yang sudah diterima saat published.
- Cards/listing/gallery/thumb **menggunakan optimized derivative**, `srcset/sizes/width/height`, stable aspect ratio, lazy off-screen, hero LCP prioritas non-lazy. Viewer/Lightbox **fetch original only after explicit open**, via authorized route; loading/failure/retry/close, focus trap/restore, Escape/arrow navigation bila sesuai, optional touch swipe tanpa merusak scroll.
- Cover = memilih satu `media.id` pada visit (tanpa file copy). Foto item bisa ditautkan ke banyak item dalam visit yang sama; cross-visit linking ditolak. Default/fallback kalau no cover/derivative failed; original tidak hilang saat retry/delete associative link. Cleanup orphan aman dan tidak menghapus original milik record lain.
- Opsi file serving efisien sesuai infrastruktur (`Storage::response`, streamed response, conditional headers) tanpa mengorbankan protected visibility. Uji image response, protected originals, same-visit invariant, upload error/retry dan unpublish revoke.

**Gate Phase 4:** tampil card optimized dan viewer original, upload pipeline/retry berjalan, media draft/private tak bisa dibuka oleh guest, unpublish menolak request baru, no broken media placeholders, no duplicate cover file.

## H. Phase 5 — Public Home, Explore, Visit/Place detail & information safety

### Routes dan pages P0

- `/` Home: cinematic editorial hero dari published feature (safe fallback), latest visits, recommendations, discovery shortcuts kota/jenis kuliner. Tidak membuat empty card or filler. Photo hierarchy dan meaningful copy.
- `/explore` default **tab Visit newest `visited_at desc, id desc`**. Tabs `Visit/Food/Place` (accessible). Search, sort sesuai tab, grid/list, result count, quick filters, advanced filters, applied chips removable, reset, pagination. Tab Food = unit `visit_item` dari eligible visit; tab Place = place dengan ≥1 eligible published visit. Jangan beri CTA redundant pada clickable cards.
- `/visit/{slug}`: place/date/hero, story/review per field yang terisi, item snapshot, unit price, quantity, taste, media, overall rating, total visit, conclusion, recommendation, Maps conditional, place link. Hide irrelevant empty sections.
- `/place/{slug}`: nama/area/address/Maps conditional, **average overall rated published visits + jumlah rated visits**, timeline historical published visits beserta masing-masing rating. Tempat dengan 0 eligible published visits = 404.

### Explore filter rules

- Query parameter dan allowlist sesuai `sitemap.md`: `tab`, `q`, `city`, `category`, `food_type`, `cuisine`, `style`, `ingredient`, `avoid_allergen`, `price_min`, `price_max`, `rating_min`, `tag`, `recommendation`, `sort`, `view`, `page`; tentukan representasi **multi-select** konsisten, URL encode/dedup/normalize, test direct URLs, Back/Forward, pagination.
- Selalu prioritaskan `q`, tabs, sort, grid/list, filter button. 3–4 quick filters relevan; advanced filter tersusun Lokasi/Makanan/Tradisi Kuliner/Teknik/Harga & Rating/Bahan & Alergen/Rekomendasi. **Progressive disclosure**; jangan munculkan 14+ checkboxes secara permanen di header atau navbar.
- Mobile filter sheet adalah accessible dialog, grouped accordion, draft state tidak mengubah URL sebelum `Tampilkan N hasil`, close/cancel discards pending changes, Reset clear, keyboard/focus/scroll lock; desktop dapat apply langsung bila state konsisten. Chips aktif & remove all tetap jelas. Filter option list dari eligible published data, tidak membocorkan taxonomy yang hanya terkait private.
- Multi-select **OR dalam satu dimensi**, **AND antardimensi**; kategori dapat include descendants; Visit/Place jika filter item-level match berarti **memiliki minimal satu hidangan yang cocok**, bukan menjadikan seluruh place/visit aman atau mempunyai cuisine tunggal. Eager load untuk output; paginate stabil; avoid cross-item matches yang salah ketika kombinasi filter seharusnya berlaku pada **hidangan yang sama**. Dokumentasikan semantik kombinasi dan uji.
- **Prices:** Visit `total_spent_idr`; Food `unit_price_idr`; Place **tidak ada price filter P0**. Jika ganti tab dan harga jadi berbeda konteks, clear price filter secara **jelas dengan feedback**; pertahankan filter yang kompatibel dan query/search, reset page; incompatible sort reset. Sorting/filter rating: Visit overall, Food taste, Place aggregate; `NULL` di akhir secara konsisten.
- Allergen exclude: `avoid_allergen` mengeluarkan hanya known `contains` & `may_contain`; unknown **tetap bisa masuk** dan ditandai *“Informasi belum tersedia”* serta disclaimer **“Informasi bahan/alergen mungkin tidak lengkap. Hasil bukan jaminan bebas alergen; konfirmasi langsung kepada penjual.”** Gunakan label *“Sembunyikan yang tercatat mengandung…”*, bukan *“Aman untuk alergi”*. Beri sumber evidence bila ada. Jangan pernah infer verified free dari absent relation, nama menu atau gambar. Perhatikan bahwa hasil Visit/Place yang lolos belum berarti semua itemnya bebas risiko.
- Cuisine per item vs cooking style per item tampil dan bisa dicari/filternya terpisah. Tag/recommendation **editorial** dari published visit terkait.
- Server central query scopes/filter builder agar Home/Explore/detail/SEO consistent, no N+1, sort whitelist, parameter max length/validation, no dynamic raw SQL user input. Batasi kombinasi filter yang ekstrem secara graceful tanpa menyembunyikan kriteria user.

**Gate Phase 5:** Alur Home → Explore tabs/filter → Visit detail → Place detail bekerja untuk guest dan konten published saja; seluruh filter/sort/URL semantics diuji; mobile Explore usable dan no allergy-safe claims.

## I. Phase 6 — SEO, system states, protection, backup/restore

- HTML headings, landmarks, informative alt (reviewed metadata; jangan mengarang foto), canonical `explore` only base canonical / `noindex,follow` untuk kombinasi filter bila diperlukan; metadata/OG dan `sitemap.xml` hanya public eligible, no admin/private. `robots.txt` bantu crawl, bukan security boundary.
- `GET /admin/visit/{id}/preview` protected/noindex; **tidak** ada public `?preview=1` bypass. Unknown/private slug gets 404. Slug redirect 301 hanya jika target masih published; cegah redirect cycle/lookup yang melanggar status.
- Branded 403, 404, 419, 429, 500, 503 with actionable recovery where safe, no stack trace/secrets. Keep consistent theme and accessibility. HTTP security headers, CSRF, session, rate limits proportional; graceful error UI jika media processor atau storage down.
- Performance: inspect actual query counts if possible, N+1, indexes sesuai `EXPLAIN` MySQL (jika tersedia), pagination; monitor representative image sizes, lazy/priority, CLS dimensions, avoid giant CSS/JS, defer noncritical. **Do not assert LCP/INP/CLS improvements without measurement**; record measurements or gaps.
- Backup/restore: implement/document **free** method for DB+media, backup metadata+manifest/consistent point-in-time strategy and safe retention. Restore test hanya pada isolated target, **jangan** overwrite active DB/media. Keep credentials outside repo; verify sample restore IDs/count, sample media, and integrity. If restore cannot execute due missing infra, provide exact commands/prereqs and mark unverified.
- Handle cache invalidation on create/publish/unpublish/revert/edit, including place aggregates, homepage, filter options, SEO. Prefer no cache until measured need; if used, explicit events + tests. Do not claim that already cached browser downloads can be retracted.

**Gate Phase 6:** no public draft/private leak including media/SEO; correct errors; backup and restore procedure genuinely executable on isolated env; build/test clean.

## J. Phase 7 — Professional QA, validation, accessibility & regression

### Automated test matrix (minimum high-risk coverage)

Create/adjust PHPUnit or Pest tests depending on installed framework; tests must be **assertive**, not smoke-only. Use factories, fixtures, fake storage/queue for unit and isolated DB for integration. Cover:

1. Auth: admin login/logout/rate limit; guest denied admin; registration disabled; CSRF and policy failure paths.
2. Draft Quick Entry: with existing place; inline-created place; empty optional fields; time default; no duplicate under repeated submit; form restoration after validation error.
3. Slug: create auto, manual override, unique collision, published rename/301, private route behavior, title change without unintended overwrite.
4. Category/food type separation; cuisine multi item-level, hierarchy/fusion; cooking style multiple; pivot dedup, category cycle prevention, restrictions on deleting in-use taxonomy.
5. Item snapshots preserved across repeat visits; `NULL` vs `0` price, visit total vs item unit price; quantity default, historical sort/filter.
6. Rating valid 1,1.5,...5 and rejects 0, 0.5, 5.5, invalid fractional increments; unrated/null; precise taste/overall/place aggregates (rated published only) and stable count, changes after publish/unpublish.
7. Published eligibility on Home, Visit, Place, Food, filter options/count, related, sitemap/OG/canonical and any media route. Private/draft/unpublished removed on new request and not present in counts or suggestions.
8. Media upload MIME/signature mismatch, too large, dimension abuse, original preservation, derivative ready/fail/retry, variant selection, cover no duplicate, cross-visit association rejection, unknown UUID, guest protected, unpublish deny original AND optimized requests.
9. Allergen: contains, may_contain, unknown, multiple selected avoidance, source displayed, no unsafe badge; filter Visit/Place association precise, and ingredient record absence not considered safe.
10. Explore default newest; tabs and filters compatible, URL canonicalization, malicious sort injection blocked, invalid parameters safely handled, pagination stability, missing prices/rating sorted consistently, Back/Forward behavior where testable.
11. Admin stepper/edit flows, scoped upload/inline taxonomy, preview protected and no publish on invalid data; error state and retry.
12. Regression: all existing tests; safe route loading, links/breadcrumbs/no dead ends; no duplicate view/component listener initialization.

### Manual / browser-based QA (where tools exist)

- Test 320, 375, 768, 1024, 1440px, 200% zoom: home/explore/detail/admin; long names, missing photos, 0 results, many active chips, portrait/landscape, many items.
- Keyboard only: skip links, tabs, combobox, dialog focus trap/restore, filter sheet apply/cancel, viewer, error focus. Reduced motion; contrast checks; meaningful empty/loading/error states.
- Visually inspect actual **rendered pages**, not only code: remove decorative divider spam, random gradients, fake lorem text, giant empty cards, mismatched same-level button sizes. Ensure editorial hero/photo and copy hierarchy readable on mobile.
- If browser/screenshots or performance tools are unavailable, **state that manual visual/metric QA is unverified** and provide exact steps/URLs for user. Do not invent screenshot, Lighthouse score, test result or performance gain.

### Final deliverables and reporting (mandatory)

Update `docs/implementation-checklist.md` with pass/fail and test references. Produce `docs/implementation-report.md` containing:

- audited repository baseline; versions, migration assumptions, reused existing pieces;
- files/routes/models/migrations/components/features created or changed, grouped by phase;
- implementation decisions & deviations from the five documents, with reason and impact;
- commands executed **and exact results** (pass/fail/skipped and blockers); DB/queue/media setup instructions;
- QA matrix and known unverified manual scenarios; security/privacy known risks;
- backup/restore steps & evidence; performance findings measured (or not measured);
- **P0 completed vs remaining**; P1/P2 intentionally deferred, plus short next actions for owner.

Run available lint/formatter, `php artisan test`, `npm run build`, route listing/migration status in the correct environment. Fix regressions caused by your changes; report preexisting failures separately. No fictitious success.

## K. Acceptance Gate — before claiming “MVP done”

Do not mark implementation complete unless **all** are true:

- [ ] Five root documents were inspected; actual repo audited, decisions recorded.
- [ ] All migrations/models/factories/tests implement correct **singular** table names and relationship integrity.
- [ ] Single admin can authenticate securely, without public registration.
- [ ] Quick Entry with place search/inline-create + local datetime + auto-slug saves minimal draft; Full Editor continues same record.
- [ ] CRUD visit/item/taxonomy, ratings/prices, media, publish/private/draft/preview implemented with server authorization.
- [ ] Rating 0.5-step input, null-ignoring averages and correct precision/1-decimal display; visit total and item unit price never confused.
- [ ] Cuisine/technique/ingredient/allergen distinct; allergen unknown not treated as absence or safety; disclaimers clear.
- [ ] Navigational images optimized, protected original viewer works, upload retry/fallback, no leaked image when unpublished.
- [ ] Home + Explore Visit/Food/Place + Visit/Place detail usable on desktop/mobile with URL-persisted filters, advanced filter progressive disclosure and correct count/sort.
- [ ] SEO/metadata/sitemap/related/filters/media expose **only** eligible published content.
- [ ] Blade layout/header/footer components shared; Alert/Modal/Toast accessible, design tokens and editorial visual established, button parity and no gratuitous decoration.
- [ ] Data safety, negative authorization tests, automated regression and available build pass; actual command results documented.
- [ ] Backup/restore database + media ready with safe verification evidence or explicitly recorded blocker.

If one requirement fails or cannot be tested, report as **INCOMPLETE / UNVERIFIED** with exact reason, affected phase, files, risks, and how to resume. Partial but verified work is preferable to silently dropping a critical feature.

## L. Execution and progress protocol

1. **Start with audit immediately** and proceed into implementation; do not just paraphrase this instruction.
2. Work phase-by-phase; after each gate provide a concise progress note: implemented, tested, blockers, next phase. Update local implementation checklist as work proceeds.
3. Resolve minor ambiguities from v1.1 defaults yourself, log them. Do not keep asking user questions that source documents already answer.
4. Protect existing user work. If a real irreversible operation or fundamental architecture conflict requires approval, stop that operation and explicitly state the blocker; continue safe independent work where possible.
5. If execution budget/context runs out, stop at a **consistent, tested checkpoint**, with precise `docs/implementation-report.md` current state and **continuation command** identifying unfinished phase. Never declare completion prematurely.
6. At final response summarize completed phases, outstanding work, tests, artifacts, and run instructions. If all P0 is done, provide admin bootstrap / app start / queue / media prerequisites in reproducible form.

**Begin now: inspect the active Foodie repository and the five v1.1 Markdown files in its root, then implement P0 according to this document.**
