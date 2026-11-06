<?php

namespace App\Services;

use App\Support\ExploreState;

class CuriosityIndex
{
    public function __construct(private ExploreQuery $explore) {}

    public function tiles(array $options): array
    {
        $tiles = [];
        $used = [];
        foreach ([
            'food' => ['key' => 'category', 'tab' => 'food', 'title' => 'Makanan & minuman', 'eyebrow' => 'Jelajahi berdasarkan'],
            'city' => ['key' => 'city', 'tab' => 'visit', 'title' => 'Kota', 'eyebrow' => 'Destinasi'],
            'style' => ['key' => 'style', 'tab' => 'food', 'title' => 'Gaya masak', 'eyebrow' => 'Teknik'],
            'type' => ['key' => 'food_type', 'tab' => 'food', 'title' => 'Jenis hidangan', 'eyebrow' => 'Hidangan'],
            'recommendation' => ['key' => 'recommendation', 'tab' => 'visit', 'title' => 'Pilihan pribadi', 'eyebrow' => 'Rekomendasi penulis'],
        ] as $name => $config) {
            $option = $options[$config['key']][0] ?? null;
            $params = ['tab' => $config['tab']];
            if ($option) {
                $params[$config['key']] = [$option['value']];
            }
            // A technique/category photograph must depict a matching item,
            // rather than an unrelated cover from a visit containing that item.
            $media = null;
            if ($option && in_array($name, ['food', 'city', 'style'], true)) {
                $entries = $this->explore->build(new ExploreState($params))
                    ->whereHas('media.variants', fn ($query) => $query->where('variant', 'card')->where('status', 'ready'))
                    ->limit(6)->get();
                $photos = $entries->flatMap(fn ($entry) => $entry->media->filter(fn ($photo) => $photo->variant('card')));
                $media = $photos->first(fn ($photo) => ! in_array($photo->id, $used, true)) ?? $photos->first();
                if ($media) {
                    $used[] = $media->id;
                }
            }
            $tiles[$name] = [
                ...$config,
                'detail' => $option['label'] ?? null,
                'url' => route('explore.index').'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986),
                'media' => $media,
            ];
        }

        return $tiles;
    }
}
