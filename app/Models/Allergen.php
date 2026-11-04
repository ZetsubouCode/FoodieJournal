<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    use HasFactory;

    protected $table = 'allergen';

    protected $guarded = ['id'];

    public function items()
    {
        return $this->belongsToMany(VisitItem::class, 'visit_item_allergen', 'allergen_id', 'visit_item_id')->withPivot('exposure_status', 'evidence_source', 'notes');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_allergen', 'allergen_id', 'ingredient_id')->withPivot('relation_status', 'notes');
    }
}
