<?php

namespace App\Support;

use App\Models\Visit;

class VisitCardTags
{
    /** Distinct, ordered Explore destinations for taxonomy actually recorded on this visit. */
    public static function for(Visit $visit): array
    {
        $tags = [];
        $seen = [];
        $add = function (string $name, string $key, string $slug) use (&$tags, &$seen): void {
            $identity = mb_strtolower(trim($name));
            if ($identity === '' || isset($seen[$identity])) {
                return;
            }
            $seen[$identity] = true;
            $tags[] = ['name' => $name, 'type' => $key, 'url' => url('explore').'?'.http_build_query([$key => [$slug]], '', '&', PHP_QUERY_RFC3986)];
        };

        foreach ($visit->items as $item) {
            if ($item->foodType?->category) {
                $term = $item->foodType->category;
                $add($term->name, 'category', $term->slug);
            }
            if ($item->foodType) {
                $add($item->foodType->name, 'food_type', $item->foodType->slug);
            }
        }
        foreach ($visit->items as $item) {
            foreach ($item->styles as $term) {
                $add($term->name, 'style', $term->slug);
            }
        }
        foreach ($visit->tags as $term) {
            $add($term->name, 'tag', $term->slug);
        }

        return $tags;
    }
}
