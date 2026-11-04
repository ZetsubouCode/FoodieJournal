<?php

namespace App\Models;

class Category extends Taxonomy
{
    protected $table = 'category';

    public function foodTypes()
    {
        return $this->hasMany(FoodType::class);
    }
}
