<?php

return [
    403 => [
        'eyebrow' => 'Akses terbatas',
        'heading' => 'Halaman ini tidak bisa diakses',
        'description' => 'Kamu tidak memiliki akses untuk melihat halaman ini.',
        'primary' => ['label' => 'Jelajahi catatan', 'url' => '/explore'],
        'secondary' => ['label' => 'Ke beranda', 'url' => '/'],
        'footer' => 'Akses terbatas',
    ],
    404 => [
        'eyebrow' => 'Halaman tidak ditemukan',
        'heading' => 'Halaman yang dicari tidak ditemukan',
        'description' => 'Mungkin alamatnya berubah atau halaman tersebut sudah tidak tersedia.',
        'primary' => ['label' => 'Jelajahi catatan', 'url' => '/explore'],
        'secondary' => ['label' => 'Ke beranda', 'url' => '/'],
        'footer' => 'Tidak ditemukan',
    ],
    419 => [
        'eyebrow' => 'Sesi berakhir',
        'heading' => 'Sesi telah berakhir',
        'description' => 'Sesi sebelumnya sudah tidak berlaku. Silakan mulai kembali untuk melanjutkan.',
        'primary' => ['label' => 'Masuk kembali', 'url' => '/admin/login'],
        'secondary' => ['label' => 'Ke beranda', 'url' => '/'],
        'footer' => 'Sesi berakhir',
    ],
    429 => [
        'eyebrow' => 'Permintaan dibatasi',
        'heading' => 'Terlalu banyak permintaan',
        'description' => 'Ada terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi nanti.',
        'primary' => ['label' => 'Ke beranda', 'url' => '/'],
        'secondary' => ['label' => 'Jelajahi catatan', 'url' => '/explore'],
        'footer' => 'Terlalu sering',
    ],
    500 => [
        'eyebrow' => 'Gangguan sementara',
        'heading' => 'Jurnal sedang mengalami gangguan',
        'description' => 'Terjadi kendala saat memuat halaman ini. Silakan coba lagi beberapa saat.',
        'primary' => [],
        'secondary' => [],
        'footer' => 'Gangguan sistem',
    ],
    503 => [
        'eyebrow' => 'Layanan sementara tidak tersedia',
        'heading' => 'Foodie sementara tidak tersedia',
        'description' => 'Jurnal sedang tidak dapat diakses. Silakan kembali beberapa saat lagi.',
        'primary' => [],
        'secondary' => [],
        'footer' => 'Layanan tidak tersedia',
    ],
];
