<?php

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitItemFactory extends Factory
{
    public function definition(): array
    {
        return ['visit_id' => Visit::factory(), 'menu_name' => fake()->randomElement(['Mie ayam bakso', 'Nasi goreng kampung', 'Es teh tawar']), 'quantity' => 1, 'unit_price_idr' => null, 'taste_rating' => null];
    }
}
