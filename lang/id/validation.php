<?php

return [
    'required' => ':attribute wajib diisi.', 'required_with' => ':attribute wajib diisi bersama :values.',
    'string' => ':attribute harus berupa teks.', 'integer' => ':attribute harus berupa bilangan bulat.',
    'numeric' => ':attribute harus berupa angka.', 'email' => 'Format email tidak valid.', 'url' => 'Tautan :attribute harus memakai http atau https.',
    'in' => 'Pilihan :attribute tidak valid.', 'exists' => 'Pilihan :attribute tidak tersedia.', 'unique' => ':attribute sudah digunakan.',
    'date_format' => 'Format :attribute tidak valid.', 'uuid' => 'Identitas formulir tidak valid. Muat ulang formulir.',
    'regex' => 'Format :attribute tidak valid.', 'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'max' => ['string' => ':attribute maksimal :max karakter.', 'numeric' => ':attribute maksimal :max.', 'array' => ':attribute maksimal :max pilihan.', 'file' => 'Ukuran berkas maksimal :max KB.'],
    'min' => ['string' => ':attribute minimal :min karakter.', 'numeric' => ':attribute minimal :min.'],
    'between' => ['numeric' => ':attribute harus di antara :min dan :max.'],
    'mimes' => 'Format foto harus JPEG, PNG, atau WebP.', 'extensions' => 'Ekstensi foto harus jpg, jpeg, png, atau webp.', 'file' => ':attribute harus berupa berkas.',
    'array' => ':attribute harus berupa daftar.', 'boolean' => 'Pilihan :attribute tidak valid.', 'distinct' => 'Pilihan :attribute tidak boleh berulang.',
    'password' => ['mixed' => 'Kata sandi perlu huruf besar dan kecil.', 'numbers' => 'Kata sandi perlu angka.'],
];
