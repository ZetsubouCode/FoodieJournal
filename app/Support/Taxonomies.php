<?php

namespace App\Support;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\RecommendationLabel;
use App\Models\Tag;

class Taxonomies
{
    public const TYPES = [
        'category' => [Category::class, 'Kategori'], 'food-type' => [FoodType::class, 'Jenis hidangan'],
        'cuisine' => [Cuisine::class, 'Asal/tradisi kuliner'], 'style' => [CookingStyle::class, 'Teknik memasak'],
        'ingredient' => [Ingredient::class, 'Bahan yang diketahui'], 'allergen' => [Allergen::class, 'Alergen'],
        'tag' => [Tag::class, 'Tag'], 'recommendation' => [RecommendationLabel::class, 'Rekomendasi editorial'],
    ];

    public static function model(string $kind): string
    {
        abort_unless(isset(self::TYPES[$kind]), 404);

        return self::TYPES[$kind][0];
    }

    public static function label(string $kind): string
    {
        self::model($kind);

        return self::TYPES[$kind][1];
    }
}
