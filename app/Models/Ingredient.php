<?php

namespace App\Models;

class Ingredient extends Taxonomy
{
    protected $table = 'ingredient';

    public function items()
    {
        return $this->belongsToMany(VisitItem::class, 'visit_item_ingredient', 'ingredient_id', 'visit_item_id');
    }

    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'ingredient_allergen', 'ingredient_id', 'allergen_id')->withPivot('relation_status', 'notes');
    }
}
