<?php

namespace App\Services;

use App\Models\FoodType;
use App\Models\VisitItem;
use App\Support\ExploreState;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscoveryMachine
{
    public function wheel(ExploreState $state): array
    {
        $types = FoodType::whereHas('items', fn ($query) => $query->publishedVisible())
            ->withCount(['items as published_count' => fn ($query) => $query->publishedVisible()])
            ->orderByDesc('published_count')->orderBy('name')->orderBy('id')->get();

        $selected = $state->get('food_type', []);
        $activeSlug = count($selected) === 1 ? $selected[0] : null;
        $limit = $types->count() > 5 ? 4 : 5;
        $featured = $types->take($limit);
        if ($activeSlug && ! $featured->contains('slug', $activeSlug)) {
            $active = $types->firstWhere('slug', $activeSlug);
            if ($active) {
                $featured = $featured->take($limit - 1)->push($active);
            }
        }

        $firstLabel = count($selected) > 1 ? 'Beberapa jenis hidangan' : ($activeSlug && ! $types->contains('slug', $activeSlug) ? 'Jenis tidak tersedia' : 'Semua rasa');
        $entries = [['kind' => 'all', 'label' => $firstLabel, 'slug' => null, 'count' => null, 'image' => null]];
        foreach ($featured as $type) {
            $item = VisitItem::publishedVisible()->where('food_type_id', $type->id)
                ->with(['media.variants', 'visit.coverMedia.variants'])
                ->orderByDesc('id')->first();
            $media = $item?->media->first(fn ($photo) => $photo->variant('thumbnail')) ?? $item?->visit->coverMedia;
            $entries[] = [
                'kind' => 'type', 'label' => $type->name, 'slug' => $type->slug,
                'count' => $type->published_count,
                'image' => $media?->variant('thumbnail') ? url('media/'.$media->uuid.'/thumbnail') : null,
            ];
        }
        if ($types->count() > 5) {
            $entries[] = ['kind' => 'more', 'label' => 'Jenis lainnya', 'slug' => null, 'count' => null, 'image' => null];
        }
        $slots = array_fill(0, 6, ['kind' => 'empty', 'label' => '', 'slug' => null, 'count' => null, 'image' => null]);
        foreach ($entries as $index => $entry) {
            $position = (int) round($index * 6 / count($entries));
            $slots[$position] = $entry;
        }

        return $slots;
    }

    public function selection(ExploreState $state, array $slots, LengthAwarePaginator $results): array
    {
        $selected = $state->get('food_type', []);
        $slug = count($selected) === 1 ? $selected[0] : null;
        $active = collect($slots)->firstWhere('slug', $slug);
        $index = $slug ? array_search($active, $slots, true) : 0;
        $label = $slug ? ($active['label'] ?? 'Jenis tidak tersedia') : $slots[0]['label'];
        $mediaFor = fn ($entry) => match ($state->get('tab')) {
            'food' => $entry->media->first(fn ($photo) => $photo->variant('card')) ?? $entry->visit?->coverMedia,
            'place' => $entry->visits->first()?->coverMedia,
            default => $entry->coverMedia,
        };
        $result = $results->getCollection()->first(fn ($entry) => $mediaFor($entry)?->variant('card')) ?? $results->first();
        $media = $result ? $mediaFor($result) : null;
        $caption = match ($state->get('tab')) {
            'food' => $result?->menu_name,
            'place' => $result?->name,
            default => $result?->display_title,
        };
        $variant = $media?->variant('hero') ?? $media?->variant('card');
        $srcset = $media?->variants->where('status', 'ready')->unique('width')
            ->map(fn ($image) => url('media/'.$media->uuid.'/'.$image->variant).' '.$image->width.'w')->join(', ');

        return [
            'index' => $index === false ? 0 : $index,
            'label' => $label,
            'count' => $results->total(),
            'image' => $variant ? url('media/'.$media->uuid.'/'.$variant->variant) : null,
            'srcset' => $srcset,
            'width' => $variant?->width ?? 960,
            'height' => $variant?->height ?? 640,
            'alt' => $media?->alt_text ?? '',
            'caption' => $caption,
        ];
    }
}
