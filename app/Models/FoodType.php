<?php

namespace App\Models;

class FoodType extends Taxonomy
{
    protected $table = 'food_type';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function items()
    {
        return $this->hasMany(VisitItem::class);
    }
}
