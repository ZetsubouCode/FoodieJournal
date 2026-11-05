<?php

namespace App\Services;

use App\Models\Visit;
use App\Support\ExploreState;

class HomepageDiscovery
{
    public function __construct(private ExploreQuery $explore) {}

    public function tiles(array $options, ?Visit $fallback): array
    {
        $tiles = [];
        foreach ([
            'food' => ['key' => 'category', 'tab' => 'food', 'title' => 'Makanan', 'eyebrow' => 'Jelajahi berdasarkan'],
            'city' => ['key' => 'city', 'tab' => 'visit', 'title' => 'Kota', 'eyebrow' => 'Destinasi'],
            'style' => ['key' => 'style', 'tab' => 'food', 'title' => 'Gaya masak', 'eyebrow' => 'Teknik'],
        ] as $name => $config) {
            $option = $options[$config['key']][0] ?? null;
            $params = ['tab' => $config['tab']];
            if ($option) {
                $params[$config['key']] = [$option['value']];
            }
            // The same filter builder as Explore keeps discovery promises and URLs aligned.
            $visit = $option ? $this->explore->build(new ExploreState(['tab' => 'visit', $config['key'] => [$option['value']]]))
                ->whereHas('coverMedia.variants', fn ($query) => $query->where('variant', 'card')->where('status', 'ready'))
                ->with(['coverMedia.variants'])->first() : null;
            $tiles[$name] = [
                ...$config,
                'detail' => $option['label'] ?? null,
                'url' => route('explore.index').'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986),
                'media' => $visit?->coverMedia ?? ($option ? null : $fallback?->coverMedia),
            ];
        }

        return $tiles;
    }
}
