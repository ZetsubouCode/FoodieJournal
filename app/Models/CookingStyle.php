<?php

namespace App\Models;

class CookingStyle extends Taxonomy
{
    protected $table = 'cooking_style';

    public function items()
    {
        return $this->belongsToMany(VisitItem::class, 'visit_item_cooking_style', 'cooking_style_id', 'visit_item_id');
    }
}
