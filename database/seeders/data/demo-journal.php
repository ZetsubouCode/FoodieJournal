<?php

// Fictional editorial fixtures. Stock photographs illustrate dishes, not actual visits.
$dish = static fn (string $name, string $type, ?int $price, ?float $rating, string $notes, string $cuisine, string $style, array $ingredients, array $allergens = [], ?string $photo = null): array => compact('name', 'type', 'price', 'rating', 'notes', 'cuisine', 'style', 'ingredients', 'allergens', 'photo');

return [
    'places' => [
        'bara-senja' => [
            'name' => 'Bara Senja', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'area' => 'Kawasan Dago', 'hour' => 19, 'total' => 238000,
            'conclusion' => 'Ingin kembali untuk duduk di teras dan mencoba menu panggang lain.',
            'items' => [
                $dish('Piring panggang campur', 'Panggang', 165000, 4.5, 'Bagian luar sedikit berasap, bagian tengah tetap empuk. Kentang panggang menyerap sausnya.', 'Kontemporer', 'Bakar', ['Ayam', 'Sapi'], ['soy' => 'contains', 'wheat' => 'may_contain'], 'grill'),
                $dish('Salad kebun kecil', 'Salad', 35000, 4, 'Daun segar dan saus ringan menjadi jeda yang enak di antara suapan daging.', 'Kontemporer', 'Segar', ['Selada', 'Tomat']),
                $dish('Teh hangat sore', 'Teh', 18000, null, 'Disajikan tanpa gula. Belum diberi penilaian rasa tersendiri.', 'Indonesia', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'mie-pagi' => [
            'name' => 'Mangkuk Mie Pagi', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'area' => 'Kawasan Blok M', 'hour' => 12, 'total' => 108000,
            'conclusion' => 'Pilihan makan siang yang menyenangkan ketika ingin kuah hangat.',
            'items' => [
                $dish('Ramen kuah shoyu', 'Mi & ramen', 68000, 4.5, 'Kuah gurih dengan akhir rasa kedelai. Mi masih kenyal dan telur setengah matang terasa lembut.', 'Jepang', 'Rebus', ['Gandum', 'Telur', 'Kedelai'], ['wheat' => 'contains', 'egg' => 'contains', 'soy' => 'contains'], 'noodles'),
                $dish('Salad wijen', 'Salad', 22000, 3.5, 'Saus wijen sedikit terlalu manis untuk selera saya, tetapi sayurnya segar.', 'Jepang', 'Segar', ['Selada', 'Wijen'], ['sesame' => 'contains']),
                $dish('Teh tawar panas', 'Teh', 8000, 4, 'Ringan, hangat, dan pas untuk menutup semangkuk kuah yang kaya rasa.', 'Jepang', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'kebun-rasa' => [
            'name' => 'Kebun Rasa', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'area' => 'Kawasan Ciumbuleuit', 'hour' => 11, 'total' => 128000,
            'conclusion' => 'Layak dicoba ketika ingin makan segar dengan suasana terang.',
            'items' => [
                $dish('Salad alpukat kebun', 'Salad', 62000, 4.5, 'Alpukat matang, daun renyah, dan tomat memberi rasa manis alami. Saus lebih enak dituang sedikit demi sedikit.', 'Kontemporer', 'Segar', ['Alpukat', 'Selada', 'Tomat'], ['sesame' => 'may_contain'], 'salad'),
                $dish('Mangkuk sayur panggang', 'Rice bowl', 42000, 4, 'Ada tekstur renyah di atas nasi hangat. Jamur panggang menjadi bagian paling gurih.', 'Kontemporer', 'Panggang oven', ['Beras', 'Jamur', 'Kedelai'], ['soy' => 'contains']),
                $dish('Teh bunga hangat', 'Teh', 16000, 4, 'Aroma lembut dan tidak terlalu pekat.', 'Kontemporer', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'meja-toscana' => [
            'name' => 'Meja Toscana', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'area' => 'Kawasan Menteng', 'hour' => 18, 'total' => 216000,
            'conclusion' => 'Pasta dan obrolan panjang menjadi alasan untuk kembali.',
            'items' => [
                $dish('Pasta jamur dan bayam', 'Pasta', 98000, 4.5, 'Pasta matang pas, saus melekat tanpa menggenang. Jamur memberi rasa gurih yang tahan lama.', 'Italia', 'Rebus', ['Gandum', 'Jamur', 'Keju'], ['wheat' => 'contains', 'milk' => 'contains', 'egg' => 'may_contain'], 'pasta'),
                $dish('Salad tomat rumah', 'Salad', 48000, 4, 'Tomat matang dan saus asam segar menyeimbangkan pasta.', 'Italia', 'Segar', ['Tomat', 'Selada']),
                $dish('Cappuccino setelah makan', 'Kopi', 38000, 4, 'Busa halus dengan rasa kopi yang masih terasa jelas.', 'Italia', 'Seduh', ['Kopi', 'Susu'], ['milk' => 'contains'], 'coffee'),
            ],
        ],
        'sushi-kecil' => [
            'name' => 'Sushi Kecil', 'city' => 'Surabaya', 'province' => 'Jawa Timur', 'area' => 'Kawasan Darmo', 'hour' => 12, 'total' => 182000,
            'conclusion' => 'Gulungan sederhana justru paling berkesan pada kunjungan ini.',
            'items' => [
                $dish('Salmon roll wijen', 'Sushi', 88000, 4.5, 'Nasi tidak terlalu padat dan ukuran gulungan nyaman untuk satu suapan. Wijen memberi aroma panggang.', 'Jepang', 'Dingin', ['Beras', 'Salmon', 'Wijen'], ['fish' => 'contains', 'sesame' => 'contains', 'soy' => 'may_contain'], 'sushi'),
                $dish('Salad kecil saus kedelai', 'Salad', 42000, 3.5, 'Saus terasa agak dominan; lain kali akan meminta disajikan terpisah.', 'Jepang', 'Segar', ['Selada', 'Kedelai'], ['soy' => 'contains']),
                $dish('Ocha hangat', 'Teh', 18000, 4, 'Seduhan ringan yang bisa dinikmati perlahan.', 'Jepang', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'tungku-napoli' => [
            'name' => 'Tungku Napoli', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta', 'area' => 'Kawasan Prawirotaman', 'hour' => 19, 'total' => 174000,
            'conclusion' => 'Pinggiran adonan yang ringan membuat saya ingin mencoba topping lain.',
            'items' => [
                $dish('Pizza margherita tungku', 'Pizza', 112000, 4.5, 'Pinggir mengembang dengan beberapa bagian gosong tipis. Saus tomat segar dan kejunya tidak berlebihan.', 'Italia', 'Panggang oven', ['Gandum', 'Tomat', 'Keju'], ['wheat' => 'contains', 'milk' => 'contains'], 'pizza'),
                $dish('Salad pendamping', 'Salad', 32000, 4, 'Daun sederhana dengan saus asam yang menyegarkan.', 'Italia', 'Segar', ['Selada', 'Tomat']),
                $dish('Teh hangat tanpa gula', 'Teh', 14000, 3.5, 'Cukup ringan, tidak menutupi rasa tomat dari pizza.', 'Indonesia', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'ruang-kopi' => [
            'name' => 'Ruang Kopi Sela', 'city' => 'Semarang', 'province' => 'Jawa Tengah', 'area' => 'Kawasan Kota Lama', 'hour' => 15, 'total' => 97000,
            'conclusion' => 'Tempat yang ingin saya datangi lagi untuk membaca sambil minum kopi.',
            'items' => [
                $dish('Cappuccino rumah', 'Kopi', 32000, 4.5, 'Rasa cokelat dan susu seimbang. Suhu minuman nyaman sejak disajikan.', 'Kontemporer', 'Seduh', ['Kopi', 'Susu'], ['milk' => 'contains'], 'coffee'),
                $dish('Croissant mentega', 'Pastry', 30000, 4, 'Lapisan luar renyah dan mudah berantakan. Bagian tengah lembut tanpa terlalu berminyak.', 'Kontemporer', 'Panggang oven', ['Gandum', 'Susu', 'Telur'], ['wheat' => 'contains', 'milk' => 'contains', 'egg' => 'contains'], 'croissant'),
                $dish('Kopi filter harian', 'Kopi', 26000, null, 'Dicicipi sedikit dari pesanan teman; harga tercatat, tetapi belum cukup untuk memberi rating.', 'Indonesia', 'Seduh', ['Kopi']),
            ],
        ],
        'oven-pagi' => [
            'name' => 'Oven Pagi', 'city' => 'Surabaya', 'province' => 'Jawa Timur', 'area' => 'Kawasan Gubeng', 'hour' => 9, 'total' => 103000,
            'conclusion' => 'Datang lebih pagi menjadi pilihan terbaik untuk mendapat pastry hangat.',
            'items' => [
                $dish('Croissant hangat', 'Pastry', 34000, 4.5, 'Lapisan tipis pecah saat dipotong, dengan aroma mentega yang jelas.', 'Kontemporer', 'Panggang oven', ['Gandum', 'Susu', 'Telur'], ['wheat' => 'contains', 'milk' => 'contains', 'egg' => 'contains'], 'croissant'),
                $dish('Pastry isi keju', 'Pastry', 36000, 4, 'Isian gurih cukup banyak. Lebih nikmat saat masih hangat.', 'Kontemporer', 'Panggang oven', ['Gandum', 'Keju'], ['wheat' => 'contains', 'milk' => 'contains']),
                $dish('Flat white pagi', 'Kopi', 28000, 4, 'Kopi terasa lebih kuat daripada susu, cocok menemani pastry.', 'Kontemporer', 'Seduh', ['Kopi', 'Susu'], ['milk' => 'contains'], 'coffee'),
            ],
        ],
        'mangkuk-hijau' => [
            'name' => 'Mangkuk Hijau', 'city' => 'Denpasar', 'province' => 'Bali', 'area' => 'Kawasan Sanur', 'hour' => 12, 'total' => 139000,
            'conclusion' => 'Mangkuk yang praktis, berwarna, dan cukup mengenyangkan.',
            'items' => [
                $dish('Rice bowl sayur dan tahu', 'Rice bowl', 72000, 4.5, 'Sayur beragam memberi banyak tekstur. Tahu di bagian atas menyerap saus dengan baik.', 'Kontemporer', 'Panggang oven', ['Beras', 'Kedelai', 'Selada', 'Alpukat'], ['soy' => 'contains', 'sesame' => 'may_contain'], 'bowl'),
                $dish('Salad tomat alpukat', 'Salad', 42000, 4, 'Rasa asam tomat menyeimbangkan alpukat yang lembut.', 'Kontemporer', 'Segar', ['Tomat', 'Alpukat']),
                $dish('Teh dingin tawar', 'Teh', 18000, 3.5, 'Tidak terlalu pekat dan terasa segar setelah makan.', 'Indonesia', 'Dingin', ['Teh'], [], 'tea'),
            ],
        ],
        'panggang-pekan' => [
            'name' => 'Panggang Akhir Pekan', 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'area' => 'Kawasan Kemang', 'hour' => 19, 'total' => 312000,
            'conclusion' => 'Layak menjadi pilihan makan malam bersama teman dengan waktu yang cukup.',
            'items' => [
                $dish('Sapi panggang dan sayuran', 'Panggang', 218000, 4.5, 'Daging mudah dipotong dan sayuran memberi rasa segar. Saus disajikan di sisi piring.', 'Kontemporer', 'Bakar', ['Sapi', 'Tomat', 'Selada'], ['milk' => 'may_contain', 'soy' => 'may_contain'], 'dinner'),
                $dish('Salad berbagi', 'Salad', 42000, 4, 'Porsi pendamping yang cukup untuk dua orang.', 'Kontemporer', 'Segar', ['Selada', 'Tomat']),
                $dish('Kopi susu penutup', 'Kopi', 32000, 4, 'Tidak diberi gula tambahan; rasa susunya cukup manis.', 'Indonesia', 'Seduh', ['Kopi', 'Susu'], ['milk' => 'contains'], 'coffee'),
            ],
        ],
        'manis-sore' => [
            'name' => 'Manis Sore', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta', 'area' => 'Kawasan Kotabaru', 'hour' => 16, 'total' => 94000,
            'conclusion' => 'Rasa asam buah membuat pencuci mulut ini lebih menarik daripada yang saya bayangkan.',
            'items' => [
                $dish('Puding susu stroberi', 'Pencuci mulut', 38000, 4.5, 'Puding lembut dengan stroberi segar di atasnya. Manisnya cukup tertahan.', 'Kontemporer', 'Dingin', ['Susu', 'Stroberi'], ['milk' => 'contains', 'egg' => 'may_contain'], 'dessert'),
                $dish('Pastry kecil untuk berbagi', 'Pastry', 28000, 3.5, 'Teksturnya masih renyah meski sudah tidak hangat.', 'Kontemporer', 'Panggang oven', ['Gandum', 'Susu'], ['wheat' => 'contains', 'milk' => 'contains']),
                $dish('Teh hitam sore', 'Teh', 18000, 4, 'Sedikit sepat, pas sebagai pasangan puding susu.', 'Indonesia', 'Seduh', ['Teh'], [], 'tea'),
            ],
        ],
        'rumah-teh' => [
            'name' => 'Rumah Teh Teduh', 'city' => 'Semarang', 'province' => 'Jawa Tengah', 'area' => 'Kawasan Candi', 'hour' => 15, 'total' => 82000,
            'conclusion' => 'Bukan tempat untuk buru-buru. Saya ingin kembali saat punya waktu membaca.',
            'items' => [
                $dish('Teh susu dan kukis', 'Teh', 36000, 4, 'Seduhan cukup kuat untuk tetap terasa di balik susu. Kukis menjadi teman minum yang sederhana.', 'Indonesia', 'Seduh', ['Teh', 'Susu', 'Gandum'], ['milk' => 'contains', 'wheat' => 'contains'], 'tea'),
                $dish('Croissant kecil', 'Pastry', 24000, 3.5, 'Bagian luar renyah, tetapi akan lebih enak jika sedikit lebih hangat.', 'Kontemporer', 'Panggang oven', ['Gandum', 'Susu'], ['wheat' => 'contains', 'milk' => 'contains'], 'croissant'),
                $dish('Seduhan teh harian', 'Teh', null, null, 'Tambahan seduhan untuk dicicipi bersama. Harga satuan dan penilaian belum dicatat.', 'Indonesia', 'Seduh', ['Teh']),
            ],
        ],
    ],
    'visits' => [
        ['key' => 'bara-di-ujung-hari', 'pros' => "Staf menjelaskan pilihan menu dengan sabar.\nTeras lebih tenang untuk mengobrol.", 'cons' => 'Hidangan utama tiba sekitar dua puluh menit setelah dipesan.', 'place' => 'bara-senja', 'title' => 'Bara, obrolan, dan makan malam yang pelan', 'story' => 'Hujan baru berhenti ketika kami sampai di Dago. Kami memilih meja teras, memesan satu piring panggang untuk berbagi, lalu membiarkan makan malam berjalan tanpa terburu-buru. Aroma asap tipis dan salad yang segar menjadi pasangan yang pas.', 'tags' => ['Makan malam', 'Bersama teman'], 'recommendations' => ['Favorit pribadi', 'Ingin kembali']],
        ['key' => 'semangkuk-hangat-blok-m', 'pros' => 'Antrean bergerak cepat meski kedai hampir penuh.', 'cons' => 'Suara dapur cukup keras dari meja kecil kami.', 'place' => 'mie-pagi', 'title' => 'Semangkuk hangat di tengah sibuknya Blok M', 'story' => 'Jam makan siang membuat kedai ini hampir penuh. Untungnya satu meja kecil masih kosong. Kuah ramen datang mengepul, dengan mi kenyal dan telur yang menjadi suapan favorit saya.', 'tags' => ['Makan siang'], 'recommendations' => ['Layak dicoba']],
        ['key' => 'hijau-di-meja-jendela', 'pros' => 'Meja dekat jendela mendapat banyak cahaya alami.', 'place' => 'kebun-rasa', 'title' => 'Makan siang hijau di meja dekat jendela', 'story' => 'Setelah berjalan pagi, saya ingin sesuatu yang lebih ringan. Salad alpukat dan semangkuk sayur panggang cukup membuat kenyang tanpa terasa berat. Cahaya dari jendela membuat waktu makan siang terasa lebih santai.', 'tags' => ['Makan siang', 'Akhir pekan'], 'recommendations' => ['Ingin kembali']],
        ['key' => 'pasta-dan-percakapan', 'pros' => 'Jarak antarmeja cukup lega untuk berbincang.', 'cons' => 'Jeda setelah hidangan pembuka cukup lama.', 'place' => 'meja-toscana', 'title' => 'Pasta jamur untuk percakapan yang panjang', 'story' => 'Kami datang lebih awal sebelum ruang makan penuh. Sepiring pasta jamur dibagi dua agar masih ada ruang untuk salad dan kopi. Sausnya sederhana, tetapi justru itu yang membuat rasa jamurnya jelas.', 'tags' => ['Makan malam', 'Bersama teman'], 'recommendations' => ['Favorit pribadi']],
        ['key' => 'gulungan-kecil-darmo', 'pros' => 'Staf segera membawakan saus tambahan saat diminta.', 'place' => 'sushi-kecil', 'title' => 'Gulungan kecil, makan siang yang menyenangkan', 'story' => 'Duduk di konter memberi kesempatan melihat pesanan disiapkan. Salmon roll menjadi pusat makan siang hari ini, sementara salad dan ocha menjaga semuanya tetap ringan.', 'tags' => ['Makan siang'], 'recommendations' => ['Layak dicoba']],
        ['key' => 'malam-di-dekat-tungku', 'pros' => 'Waktu tunggu disampaikan sejak awal.', 'place' => 'tungku-napoli', 'title' => 'Malam hangat di dekat tungku pizza', 'story' => 'Kami memesan margherita untuk menguji hal yang paling sederhana: adonan, tomat, dan keju. Pinggirannya ringan dengan aroma panggang yang menyenangkan. Satu loyang habis lebih cepat daripada perkiraan.', 'tags' => ['Makan malam', 'Akhir pekan'], 'recommendations' => ['Ingin kembali']],
        ['key' => 'jeda-kopi-kota-lama', 'pros' => 'Sudut dekat rak buku nyaman untuk duduk sendiri.', 'place' => 'ruang-kopi', 'title' => 'Jeda kopi di antara langkah Kota Lama', 'story' => 'Berjalan tanpa tujuan membawa saya ke kedai berjendela tinggi ini. Cappuccino dan croissant menjadi alasan untuk membuka buku beberapa halaman lebih lama. Kopi filter teman hanya sempat saya cicipi, jadi belum saya beri nilai.', 'tags' => ['Ngopi'], 'recommendations' => ['Favorit pribadi', 'Ingin kembali']],
        ['key' => 'remah-di-pagi-hari', 'cons' => 'Antrean bawa pulang membuat area dekat pintu cukup sibuk.', 'place' => 'oven-pagi', 'title' => 'Pagi yang dimulai dari remah croissant', 'story' => 'Pastry pertama baru keluar saat saya tiba. Croissant hangat membuat meja penuh remah dalam hitungan menit. Kopi sederhana menjadi pasangan yang tepat untuk sarapan yang tidak direncanakan ini.', 'tags' => ['Sarapan', 'Akhir pekan'], 'recommendations' => ['Layak dicoba']],
        ['key' => 'warna-dalam-mangkuk', 'pros' => 'Ruang terbuka terasa sejuk.', 'cons' => 'Lalu lintas sesekali terdengar dari meja kami.', 'place' => 'mangkuk-hijau', 'title' => 'Banyak warna dalam satu mangkuk', 'story' => 'Setelah berjalan di Sanur, kami mencari makan siang yang mudah dibagi dan tidak terlalu berat. Mangkuk nasi dengan tahu dan sayuran memberi cukup banyak tekstur. Saus terpisah membuat rasa setiap bahan masih terasa.', 'tags' => ['Makan siang', 'Bersama teman'], 'recommendations' => ['Ingin kembali']],
        ['key' => 'meja-besar-akhir-pekan', 'pros' => 'Meja panjang memudahkan kami duduk bersama.', 'cons' => 'Ruang makan cukup bising saat penuh.', 'place' => 'panggang-pekan', 'title' => 'Satu meja besar untuk cerita akhir pekan', 'story' => 'Makan malam kali ini lebih tentang berkumpul daripada mencoba sebanyak mungkin menu. Sapi panggang dan salad cukup menjadi pusat meja. Kopi datang ketika percakapan justru baru mulai ramai.', 'tags' => ['Makan malam', 'Akhir pekan', 'Bersama teman'], 'recommendations' => ['Layak dicoba']],
        ['key' => 'stroberi-menjelang-sore', 'pros' => 'Sudut dekat jendela menghadap halaman yang tenang.', 'place' => 'manis-sore', 'title' => 'Sedikit manis, sedikit asam menjelang sore', 'story' => 'Kami singgah setelah makan siang dan memilih puding susu dengan stroberi. Porsinya kecil, cukup untuk menutup makan tanpa berlebihan. Teh hitam ternyata menjadi pasangan yang paling pas.', 'tags' => ['Akhir pekan', 'Bersama teman'], 'recommendations' => ['Favorit pribadi']],
        ['key' => 'teko-dan-buku', 'pros' => 'Halaman rindang dan tenang menjelang sore.', 'place' => 'rumah-teh', 'title' => 'Satu teko teh dan beberapa halaman buku', 'story' => 'Tidak ada rencana khusus sore ini selain duduk sebentar. Teko teh, kukis, dan halaman yang teduh membuat sebentar berubah menjadi satu jam. Ada tambahan seduhan yang saya cicipi, tetapi harga dan nilainya belum tercatat.', 'tags' => ['Akhir pekan'], 'recommendations' => ['Ingin kembali']],
        ['key' => 'kembali-ke-bara', 'place' => 'bara-senja', 'title' => 'Kembali ke teras, kali ini tanpa hujan', 'story' => 'Kunjungan kedua memberi suasana berbeda: teras kering dan langit masih terang. Saya memesan piring yang sama untuk membandingkan rasa. Daging terasa sedikit lebih matang, tetapi salad masih menjadi pendamping yang menyenangkan.', 'tags' => ['Makan malam', 'Kunjungan ulang'], 'total' => 246000],
        ['key' => 'ramen-sebelum-antrean', 'pros' => 'Sebelum jam makan siang, antrean dan suasana kedai lebih tenang.', 'place' => 'mie-pagi', 'title' => 'Datang lebih awal untuk semangkuk ramen', 'story' => 'Datang sebelum jam makan siang ternyata membuat semuanya lebih tenang. Kuah terasa konsisten dengan kunjungan sebelumnya. Kali ini saya bisa menikmati mi sebelum percakapan kedai menjadi terlalu ramai.', 'tags' => ['Makan siang', 'Kunjungan ulang']],
        ['key' => 'kopi-saat-gerimis', 'place' => 'ruang-kopi', 'title' => 'Menunggu gerimis reda bersama kopi', 'story' => 'Rencana berjalan sore tertunda oleh gerimis. Saya kembali ke meja dekat rak buku dan memesan cappuccino. Croissant hari ini sedikit lebih gelap panggangannya, dengan aroma mentega yang masih menyenangkan.', 'tags' => ['Ngopi', 'Kunjungan ulang'], 'recommendations' => ['Ingin kembali'], 'total' => 101000],
        ['key' => 'pizza-untuk-berdua', 'place' => 'tungku-napoli', 'title' => 'Margherita yang cukup untuk berdua', 'story' => 'Kunjungan ulang kali ini lebih singkat. Kami berbagi margherita dan salad sebelum melanjutkan perjalanan. Tungku masih sibuk, tetapi pesanan datang lebih cepat daripada terakhir kali.', 'tags' => ['Makan malam', 'Kunjungan ulang']],
        ['key' => 'salad-setelah-hujan', 'place' => 'kebun-rasa', 'title' => 'Meja jendela setelah hujan reda', 'story' => 'Cahaya siang kembali masuk setelah hujan berhenti. Salad alpukat masih menjadi pesanan utama, kali ini dengan lebih sedikit saus. Rasanya terasa lebih seimbang dan sayurannya tetap renyah.', 'tags' => ['Makan siang', 'Kunjungan ulang'], 'recommendations' => ['Layak dicoba']],
        ['key' => 'pastry-untuk-pagi-lambat', 'place' => 'oven-pagi', 'title' => 'Sarapan pelan sebelum kota ramai', 'story' => 'Saya datang lebih pagi untuk menghindari antrean bawa pulang. Pastry keju kali ini terasa lebih hangat dan gurih. Kami membagi semuanya agar bisa mencicipi tanpa memesan terlalu banyak.', 'tags' => ['Sarapan', 'Kunjungan ulang']],
        ['key' => 'teh-tanpa-kamera', 'place' => 'rumah-teh', 'title' => 'Sore tanpa kamera, hanya teh dan cerita', 'story' => 'Ponsel tetap di tas selama kunjungan ini, jadi tidak ada foto untuk dicatat. Kami mengobrol di halaman dan menghabiskan satu teko teh sampai hampir dingin. Jumlah pembayaran juga belum sempat saya tulis.', 'tags' => ['Akhir pekan', 'Kunjungan ulang'], 'without_photos' => true, 'total' => null],
        ['key' => 'pasta-makan-siang-singkat', 'pros' => 'Staf melayani pesanan lebih cepat pada kunjungan ini.', 'cons' => 'Ruang makan terasa lebih ramai dari sebelumnya.', 'place' => 'meja-toscana', 'title' => 'Makan siang singkat dengan pasta favorit', 'story' => 'Tidak selalu perlu mencoba menu baru. Sepiring pasta jamur yang sama menjadi makan siang yang cukup nyaman di antara dua urusan. Pelayanan hari ini lebih cepat, meski ruang makan terasa lebih ramai.', 'tags' => ['Makan siang', 'Kunjungan ulang']],
        ['key' => 'draft-catatan-manis', 'place' => 'manis-sore', 'title' => 'Draft: catatan sore yang belum selesai', 'story' => 'Masih perlu melengkapi cerita, memeriksa catatan harga, dan memilih foto sampul sebelum diterbitkan.', 'status' => 'draft', 'tags' => ['Akhir pekan'], 'total' => null],
        ['key' => 'private-makan-malam', 'place' => 'panggang-pekan', 'title' => 'Privat: makan malam kecil bersama teman', 'story' => 'Catatan pribadi untuk mengingat menu yang dipesan bersama teman. Disimpan privat dan tidak ditampilkan di jurnal publik.', 'status' => 'private', 'tags' => ['Makan malam', 'Bersama teman']],
    ],
];
