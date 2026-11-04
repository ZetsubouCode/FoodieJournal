<?php

namespace App\Models;

class Cuisine extends Taxonomy
{
    protected $table = 'cuisine';

    public function items()
    {
        return $this->belongsToMany(VisitItem::class, 'visit_item_cuisine', 'cuisine_id', 'visit_item_id');
    }
}
