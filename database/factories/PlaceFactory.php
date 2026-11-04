<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlaceFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Warung '.fake()->unique()->lastName(), 'city_name' => fake()->randomElement(['Semarang', 'Jakarta', 'Bandung'])];
    }
}
