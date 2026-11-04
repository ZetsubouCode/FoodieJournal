<?php

namespace App\Support;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\Place;
use App\Models\RecommendationLabel;
use App\Models\Tag;

class PublicOptions
{
    public const LABELS = ['city' => 'Kota', 'category' => 'Kategori', 'food_type' => 'Jenis hidangan', 'cuisine' => 'Asal/tradisi kuliner', 'style' => 'Teknik memasak', 'ingredient' => 'Bahan yang diketahui', 'avoid_allergen' => 'Sembunyikan yang tercatat mengandung', 'tag' => 'Tag', 'recommendation' => 'Rekomendasi editorial'];

    public function all(): array
    {
        $options = [];
        $options['city'] = Place::publishedVisible()->whereNotNull('city_name')->select('city_name', 'city_slug')->distinct()->orderBy('city_name')->get()->map(fn ($p) => ['value' => $p->city_slug, 'label' => $p->city_name])->all();
        foreach (['food_type' => FoodType::class, 'cuisine' => Cuisine::class, 'style' => CookingStyle::class, 'ingredient' => Ingredient::class, 'avoid_allergen' => Allergen::class] as $key => $class) {
            $terms = $class::whereHas('items', fn ($q) => $q->publishedVisible())->get();
            if ($key === 'cuisine') {
                $terms = $this->ancestors($class, $terms);
            }
            $options[$key] = $terms->sortBy($key === 'avoid_allergen' ? 'label_id' : 'name')->map(fn ($t) => ['value' => $key === 'avoid_allergen' ? $t->code : $t->slug, 'label' => $key === 'avoid_allergen' ? $t->label_id : $t->name])->values()->all();
        }
        $categories = Category::whereHas('foodTypes.items', fn ($q) => $q->publishedVisible())->get();
        $options['category'] = $this->ancestors(Category::class, $categories)->sortBy('name')->map(fn ($t) => ['value' => $t->slug, 'label' => $t->name])->values()->all();
        foreach (['tag' => Tag::class, 'recommendation' => RecommendationLabel::class] as $key => $class) {
            $options[$key] = $class::whereHas('visits', fn ($q) => $q->publishedVisible())->orderBy('name')->get()->map(fn ($t) => ['value' => $t->slug, 'label' => $t->name])->all();
        }
        if (collect($options['avoid_allergen'])->contains(fn ($v) => in_array($v['value'], ['wheat', 'rye', 'barley', 'oat']))) {
            $options['avoid_allergen'][] = ['value' => 'cereals_gluten', 'label' => 'Serealia mengandung gluten (kelompok EU)'];
        }

        return $options;
    }

    private function ancestors(string $class, $terms)
    {
        $ids = $terms->pluck('parent_id')->filter()->unique();
        for ($depth = 0; $depth < 10 && $ids->isNotEmpty(); $depth++) {
            $parents = $class::whereIn('id', $ids)->whereNotIn('id', $terms->modelKeys())->get();
            $terms = $terms->merge($parents);
            $ids = $parents->pluck('parent_id')->filter()->unique();
        }

        return $terms;
    }
}
