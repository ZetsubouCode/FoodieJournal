<?php

namespace App\Support;

class ExploreState
{
    public const DIMENSIONS = ['city', 'category', 'food_type', 'cuisine', 'style', 'ingredient', 'avoid_allergen', 'tag', 'recommendation'];

    public const SORTS = [
        'visit' => ['newest' => 'Kunjungan terbaru', 'oldest' => 'Kunjungan terlama', 'rating_high' => 'Rating tertinggi', 'rating_low' => 'Rating terendah', 'price_low' => 'Total terendah', 'price_high' => 'Total tertinggi'],
        'food' => ['newest' => 'Kunjungan terbaru', 'rating_high' => 'Rasa tertinggi', 'rating_low' => 'Rasa terendah', 'price_low' => 'Harga satuan terendah', 'price_high' => 'Harga satuan tertinggi'],
        'place' => ['recent_visit' => 'Terakhir dikunjungi', 'rating_high' => 'Rating tertinggi', 'rating_low' => 'Rating terendah', 'name_asc' => 'Nama A–Z'],
    ];

    public array $values = [];

    public array $notices = [];

    public function __construct(array $input)
    {
        $tab = is_string($input['tab'] ?? null) && isset(self::SORTS[$input['tab']]) ? $input['tab'] : 'visit';
        $this->values = ['tab' => $tab, 'view' => in_array($input['view'] ?? null, ['grid', 'list'], true) ? $input['view'] : 'grid'];
        $default = array_key_first(self::SORTS[$tab]);
        $this->values['sort'] = is_string($input['sort'] ?? null) && isset(self::SORTS[$tab][$input['sort']]) ? $input['sort'] : $default;
        if (is_string($input['q'] ?? null) && trim($input['q']) !== '') {
            $this->values['q'] = mb_substr(preg_replace('/\s+/u', ' ', trim(mb_convert_encoding($input['q'], 'UTF-8', 'UTF-8'))), 0, 120);
        }
        foreach (self::DIMENSIONS as $key) {
            if (! isset($input[$key])) {
                continue;
            }
            $values = is_array($input[$key]) ? $input[$key] : [$input[$key]];
            if (count($values) > 20) {
                $this->notices[] = 'Maksimal 20 pilihan per kelompok filter. Kurangi pilihan lalu terapkan kembali.';

                continue;
            }
            $clean = array_values(array_unique(array_filter($values, fn ($v) => is_string($v) && preg_match('/^[a-z0-9][a-z0-9_-]{0,159}$/', $v))));
            if ($clean) {
                $this->values[$key] = $clean;
            }
        }
        foreach (['price_min', 'price_max'] as $key) {
            if ($tab !== 'place' && isset($input[$key]) && is_scalar($input[$key]) && preg_match('/^\d{1,12}$/', (string) $input[$key])) {
                $this->values[$key] = (int) $input[$key];
            }
        }
        if (isset($this->values['price_min'], $this->values['price_max']) && $this->values['price_min'] > $this->values['price_max']) {
            unset($this->values['price_min'], $this->values['price_max']);
            $this->notices[] = 'Rentang harga tidak valid. Minimum harus lebih kecil dari maksimum; terapkan kembali.';
        }
        if (isset($input['rating_min']) && is_scalar($input['rating_min']) && is_numeric($input['rating_min']) && $input['rating_min'] >= 1 && $input['rating_min'] <= 5) {
            $this->values['rating_min'] = (float) $input['rating_min'];
        }
        if (isset($input['page']) && is_scalar($input['page']) && ctype_digit((string) $input['page']) && $input['page'] > 0 && $input['page'] < 100000) {
            $this->values['page'] = (int) $input['page'];
        }
        if (($input['price_reset'] ?? null) === '1' || ($tab === 'place' && (isset($input['price_min']) || isset($input['price_max'])))) {
            $this->notices[] = 'Filter harga dihapus karena konteks tab berubah. Harga hidangan dan total kunjungan berbeda.';
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function url(array $changes = [], array $remove = []): string
    {
        $values = array_merge($this->values, $changes);
        foreach ($remove as $key) {
            unset($values[$key]);
        }

        return url('/explore').'?'.http_build_query($values, '', '&', PHP_QUERY_RFC3986);
    }

    public function tabUrl(string $tab): string
    {
        $changes = ['tab' => $tab, 'sort' => array_key_first(self::SORTS[$tab])];
        if ($tab !== $this->get('tab') && (isset($this->values['price_min']) || isset($this->values['price_max']))) {
            $changes['price_reset'] = 1;
        }

        return $this->url($changes, ['page', 'price_min', 'price_max']);
    }

    public function chips(array $options): array
    {
        $chips = [];
        foreach (array_merge(self::DIMENSIONS, ['q', 'price_min', 'price_max', 'rating_min']) as $key) {
            if (! array_key_exists($key, $this->values)) {
                continue;
            }
            foreach ((array) $this->values[$key] as $value) {
                $remaining = array_values(array_filter((array) $this->values[$key], fn ($v) => $v !== $value));
                $url = $remaining ? $this->url([$key => $remaining], ['page']) : $this->url([], [$key, 'page']);
                $labels = ['q' => 'Cari', 'price_min' => 'Harga minimum', 'price_max' => 'Harga maksimum', 'rating_min' => 'Rating minimum'];
                $label = collect($options[$key] ?? [])->firstWhere('value', $value)['label'] ?? $value;
                $chips[] = ['label' => ($labels[$key] ?? (PublicOptions::LABELS[$key] ?? $key)).': '.$label, 'url' => $url];
            }
        }

        return $chips;
    }
}
