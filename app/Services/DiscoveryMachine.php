<?php

namespace App\Services;

use App\Support\ExploreState;
use App\Support\PublicOptions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DiscoveryMachine
{
    public function slots(ExploreState $state, array $options): array
    {
        $presets = [];
        foreach (config('discovery.presets', []) as $preset) {
            if ($this->validPreset($preset, $options)) {
                $presets[] = [...$preset, 'kind' => 'Koleksi'];
            }
        }
        $presets[] = ['id' => 'latest', 'label' => 'Catatan terbaru', 'kind' => 'Arsip', 'filters' => []];
        // Small, real shortcuts; the complete taxonomy remains in the console.
        foreach (['food_type', 'recommendation', 'city', 'category', 'style'] as $dimension) {
            foreach (array_slice($options[$dimension], 0, $dimension === 'food_type' ? 2 : 1) as $option) {
                $filters = [$dimension => [$option['value']]];
                if (! collect($presets)->contains(fn ($preset) => $preset['filters'] === $filters)) {
                    $presets[] = ['id' => $dimension.'-'.$option['value'], 'label' => $option['label'], 'kind' => PublicOptions::LABELS[$dimension], 'filters' => $filters];
                }
            }
        }
        $slots = [];
        foreach (array_slice($presets, 0, 6) as $index => $preset) {
            $base = new ExploreState($this->withoutPreset($state, $presets));
            $combined = collect($base->values)->except('page')->all();
            foreach ($preset['filters'] as $dimension => $values) {
                $combined[$dimension] = array_values(array_unique([...$base->get($dimension, []), ...$values]));
            }
            $target = new ExploreState([...$combined, 'collection' => $preset['id']]);
            $presetState = new ExploreState(['tab' => $state->get('tab'), ...$preset['filters']]);
            $active = ($state->get('collection') === $preset['id'] || (! $state->get('collection') && $this->criteria($state) === $this->criteria($presetState)))
                && collect($preset['filters'])->every(fn ($values, $key) => ! array_diff($values, $state->get($key, [])))
                && ($preset['id'] !== 'latest' || $state->get('sort') === $target->get('sort'));
            // A bounded preview uses exactly the result/filter engine used by
            // browsing. No unrelated stock photo, separate taxonomy query or
            // invented collection count is supplied to a compartment.
            $previewState = new ExploreState(['tab' => $state->get('tab'), ...$preset['filters']]);
            $matches = app(ExploreQuery::class)->build($previewState)->limit(4)->get();
            $preview = $this->selection($previewState, [], new LengthAwarePaginator($matches, $matches->count(), 4));
            $slots[] = [...$preset, 'code' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'][$index], 'url' => $target->url(), 'active' => $active, 'image' => $preview['thumbnail']];
        }
        while (count($slots) < 6) {
            $slots[] = ['id' => 'empty-'.count($slots), 'code' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'][count($slots)], 'kind' => 'Arsip', 'label' => 'Belum ada koleksi', 'url' => null, 'active' => false, 'image' => null];
        }

        return $slots;
    }

    private function criteria(ExploreState $state): array
    {
        $values = collect($state->values)->except(['tab', 'view', 'sort', 'page', 'collection'])->all();
        foreach ($values as &$value) {
            if (is_array($value)) {
                sort($value);
            }
        }
        ksort($values);

        return $values;
    }

    public function withoutPreset(ExploreState $state, array $presets): array
    {
        $values = collect($state->values)->except(['collection', 'page'])->all();
        $preset = collect($presets)->firstWhere('id', $state->get('collection'));
        foreach ($preset['filters'] ?? [] as $dimension => $selected) {
            $remaining = array_values(array_diff($values[$dimension] ?? [], $selected));
            if ($remaining) {
                $values[$dimension] = $remaining;
            } else {
                unset($values[$dimension]);
            }
        }

        return $values;
    }

    public function surprise(ExploreState $state, ?int $exclude = null): array
    {
        $query = app(ExploreQuery::class)->build($state)->reorder();
        $total = (clone $query)->count();
        if ($total > 1 && $exclude) {
            $query->whereKeyNot($exclude);
        }
        $chosen = $query->inRandomOrder()->limit(1)->get();

        return $this->selection($state, [], new LengthAwarePaginator($chosen, $total, 1));
    }

    private function validPreset(array $preset, array $options): bool
    {
        if (empty($preset['id']) || empty($preset['label']) || empty($preset['filters']) || ! is_array($preset['filters'])) {
            return false;
        }
        foreach ($preset['filters'] as $dimension => $values) {
            if (! in_array($dimension, ExploreState::DIMENSIONS, true) || ! is_array($values) || ! $values || array_diff($values, array_column($options[$dimension], 'value'))) {
                return false;
            }
        }

        return true;
    }

    public function selection(ExploreState $state, array $slots, LengthAwarePaginator $results): array
    {
        $active = collect($slots)->firstWhere('active', true);
        $label = $active['label'] ?? ($this->criteria($state) === [] ? 'Catatan terbaru' : 'Pilihan pencarianmu');
        $tab = $state->get('tab');
        $result = $results->first();
        $media = null;
        $item = null;
        $hasItemFilters = collect(['category', 'food_type', 'cuisine', 'style', 'ingredient', 'avoid_allergen'])->contains(fn ($key) => filled($state->get($key)));
        if ($result && ($hasItemFilters || $tab === 'food')) {
            $items = app(ExploreQuery::class)->previewItems($state);
            $ids = $results->getCollection()->modelKeys();
            match ($tab) {
                'food' => $items->whereIn('visit_item.id', $ids),
                'place' => $items->whereHas('visit', fn ($visit) => $visit->whereIn('place_id', $ids)),
                default => $items->whereIn('visit_id', $ids),
            };
            $item = $items->whereHas('media.variants', fn ($photo) => $photo->where('variant', 'card')->where('status', 'ready'))->orderByDesc('visit_item.id')->first();
            $media = $item?->media->first(fn ($photo) => $photo->variant('card'));
            if ($item) {
                $result = $results->firstWhere('id', match ($tab) {
                    'food' => $item->id, 'place' => $item->visit->place_id, default => $item->visit_id
                });
            }
        } elseif ($result && $tab === 'place') {
            // A place's latest visit may not carry the selected recommendation/tag.
            $visitState = new ExploreState([...collect($state->values)->except(['q', 'page', 'rating_min'])->all(), 'tab' => 'visit']);
            $matchingVisits = app(ExploreQuery::class)->build($visitState)->whereIn('place_id', $results->getCollection()->modelKeys());
            $featuredVisit = (clone $matchingVisits)->whereHas('media.variants', fn ($photo) => $photo->where('variant', 'card')->where('status', 'ready'))->first() ?? $matchingVisits->first();
            $result = $results->firstWhere('id', $featuredVisit?->place_id) ?? $result;
            $media = $featuredVisit?->coverMedia?->variant('card') ? $featuredVisit->coverMedia : $featuredVisit?->media->first(fn ($photo) => $photo->variant('card'));
        } elseif ($result) {
            $result = $results->first(fn ($entry) => $entry->coverMedia?->variant('card') || $entry->media->contains(fn ($photo) => $photo->variant('card'))) ?? $result;
            $media = $result->coverMedia?->variant('card') ? $result->coverMedia : $result->media->first(fn ($photo) => $photo->variant('card'));
        }
        $visit = match ($tab) {
            'food' => $result?->visit, 'place' => $item?->visit ?? $featuredVisit ?? $result?->visits->first(), default => $result
        };
        $place = $tab === 'place' ? $result : $visit?->place;
        $variant = $media?->variant('hero') ?? $media?->variant('card');

        return [
            'slot' => $active['id'] ?? ($this->criteria($state) === [] ? 'latest' : ''), 'code' => $active['code'] ?? null, 'label' => $label, 'count' => $results->total(),
            'image' => $variant ? url('media/'.$media->uuid.'/'.$variant->variant) : null,
            'thumbnail' => ($thumb = $media?->variant('thumbnail') ?? $media?->variant('card')) ? url('media/'.$media->uuid.'/'.$thumb->variant) : null,
            'srcset' => $media?->variants->where('status', 'ready')->unique('width')->map(fn ($image) => url('media/'.$media->uuid.'/'.$image->variant).' '.$image->width.'w')->join(', '),
            'width' => $variant?->width ?? 960, 'height' => $variant?->height ?? 640,
            'alt' => $media?->alt_text ?: ($item?->menu_name ?? $visit?->display_title ?? ''),
            'object_id' => $result?->id, 'mode' => $tab,
            'title' => match ($tab) {
                'food' => $result?->menu_name, 'place' => $place?->name, default => $visit?->display_title
            },
            'place' => $place?->name, 'city' => $place?->city_name,
            'excerpt' => $visit ? Str::limit(strip_tags($visit->experience ?? ''), 150) : null,
            'date' => $visit?->visited_at?->setTimezone(config('foodie.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y'),
            'href' => match ($tab) {
                'food' => $visit && $result ? url('visit/'.$visit->slug).'#hidangan-'.$result->id : null,
                'place' => $place ? url('place/'.$place->slug) : null,
                default => $visit ? url('visit/'.$visit->slug) : null,
            },
        ];
    }
}
