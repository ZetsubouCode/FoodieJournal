<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VisitFactory extends Factory
{
    public function definition(): array
    {
        return ['place_id' => Place::factory(), 'user_id' => User::factory(), 'creation_token' => (string) Str::uuid(), 'title' => 'Catatan '.fake()->unique()->sentence(3), 'visited_at' => now()->subDays(fake()->numberBetween(0, 100)), 'status' => 'draft', 'experience' => 'Catatan pengalaman makan pribadi.'];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }

    public function private(): static
    {
        return $this->state(fn () => ['status' => 'private']);
    }
}
