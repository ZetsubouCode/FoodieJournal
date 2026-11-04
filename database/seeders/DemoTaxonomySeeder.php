<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CookingStyle;
use App\Models\Cuisine;
use App\Models\FoodType;
use App\Models\Ingredient;
use App\Models\RecommendationLabel;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AllergenSeeder::class);

        foreach (['Makanan', 'Minuman', 'Camilan & pencuci mulut'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        foreach (['Hidangan utama' => 'Makanan', 'Roti & pastry' => 'Camilan & pencuci mulut'] as $name => $parent) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'parent_id' => Category::where('slug', Str::slug($parent))->sole()->id]);
        }
        foreach ([
            'Panggang' => 'Hidangan utama', 'Mi & ramen' => 'Hidangan utama',
            'Salad' => 'Hidangan utama', 'Pasta' => 'Hidangan utama',
            'Sushi' => 'Hidangan utama', 'Pizza' => 'Hidangan utama',
            'Rice bowl' => 'Hidangan utama', 'Kopi' => 'Minuman', 'Teh' => 'Minuman',
            'Pastry' => 'Roti & pastry', 'Pencuci mulut' => 'Camilan & pencuci mulut',
        ] as $name => $category) {
            FoodType::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'category_id' => Category::where('slug', Str::slug($category))->sole()->id]);
        }
        $asia = Cuisine::firstOrCreate(['slug' => 'asia'], ['name' => 'Asia', 'classification' => 'region']);
        foreach (['Indonesia', 'Jepang'] as $name) {
            Cuisine::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'parent_id' => $asia->id, 'classification' => 'tradition']);
        }
        Cuisine::firstOrCreate(['slug' => 'italia'], ['name' => 'Italia', 'classification' => 'tradition']);
        Cuisine::firstOrCreate(['slug' => 'kontemporer'], ['name' => 'Kontemporer', 'classification' => 'editorial']);

        foreach ([
            CookingStyle::class => ['Bakar', 'Rebus', 'Panggang oven', 'Seduh', 'Segar', 'Dingin'],
            Ingredient::class => ['Ayam', 'Sapi', 'Salmon', 'Telur', 'Susu', 'Keju', 'Gandum', 'Beras', 'Kedelai', 'Selada', 'Tomat', 'Alpukat', 'Jamur', 'Kopi', 'Teh', 'Stroberi', 'Wijen'],
            Tag::class => ['Makan siang', 'Makan malam', 'Sarapan', 'Ngopi', 'Akhir pekan', 'Bersama teman', 'Kunjungan ulang'],
            RecommendationLabel::class => ['Ingin kembali', 'Favorit pribadi', 'Layak dicoba'],
        ] as $model => $names) {
            foreach ($names as $order => $name) {
                $model::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $order]);
            }
        }
    }
}
