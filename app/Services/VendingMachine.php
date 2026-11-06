<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Support\Str;

/** Published visits only; the cabinet never invents a sample or repeats a filler. */
class VendingMachine
{
    public function page(int $page = 1): array
    {
        $query = Visit::publishedVisible();
        $total = (clone $query)->count();
        $pages = max(1, (int) ceil($total / 6));
        $page = max(1, min($pages, $page));
        $visits = $query->with(['place', 'coverMedia.variants', 'media.variants'])
            ->orderByDesc('visited_at')->orderByDesc('id')->forPage($page, 6)->get();

        return [
            'page' => $page, 'pages' => $pages, 'total' => $total,
            'entries' => $visits->map(function (Visit $visit, int $index) {
                // A corrupt/cross-visit cover must not leak another visit's media.
                $photo = $visit->coverMedia?->visit_id === $visit->id && $visit->coverMedia?->variant('card')
                    ? $visit->coverMedia : $visit->media->first(fn ($media) => $media->variant('card'));
                $variant = $photo?->variant('card');

                return [
                    'id' => $visit->id, 'code' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'][$index],
                    'title' => $visit->display_title, 'place' => $visit->place->name,
                    'city' => $visit->place->city_name,
                    'date' => $visit->visited_at->setTimezone(config('foodie.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y'),
                    'excerpt' => Str::limit(strip_tags($visit->experience ?? ''), 155),
                    'href' => route('visit.show', $visit->slug),
                    'image' => $variant ? url('media/'.$photo->uuid.'/card') : null,
                    'width' => $variant?->width ?? 720, 'height' => $variant?->height ?? 480,
                ];
            })->all(),
        ];
    }
}
