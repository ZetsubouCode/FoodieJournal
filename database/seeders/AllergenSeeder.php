<?php

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;

class AllergenSeeder extends Seeder
{
    public function run(): void
    {
        // FDA9 and EU14 union with gluten cereals split: wheat is not the entire EU group.
        $terms = [
            'milk' => ['Susu', 'Milk'], 'egg' => ['Telur', 'Egg'], 'fish' => ['Ikan', 'Fish'],
            'crustacean' => ['Krustasea', 'Crustaceans'], 'tree_nut' => ['Kacang pohon', 'Tree nuts'],
            'peanut' => ['Kacang tanah', 'Peanut'], 'wheat' => ['Gandum', 'Wheat'],
            'soy' => ['Kedelai', 'Soy'], 'sesame' => ['Wijen', 'Sesame'], 'celery' => ['Seledri', 'Celery'],
            'mustard' => ['Mustard', 'Mustard'], 'sulphite' => ['Sulfit', 'Sulphites'],
            'lupin' => ['Lupin', 'Lupin'], 'mollusc' => ['Moluska', 'Molluscs'],
            'rye' => ['Rye', 'Rye'], 'barley' => ['Barley', 'Barley'], 'oat' => ['Oat', 'Oat'],
        ];
        foreach ($terms as $code => [$id, $en]) {
            Allergen::firstOrCreate(['code' => $code], ['label_id' => $id, 'label_en' => $en, 'sort_order' => array_search($code, array_keys($terms)), 'reference_url' => 'https://www.fda.gov/food/buy-store-serve-safe-food/food-allergies-what-you-need-know ; https://food.ec.europa.eu/food-safety/campaign-2026/allergies_en']);
        }
    }
}
