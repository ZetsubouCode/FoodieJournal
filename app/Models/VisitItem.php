<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitItem extends Model
{
    use HasFactory;

    protected $table = 'visit_item';

    protected $fillable = ['food_type_id', 'menu_name', 'quantity', 'unit_price_idr', 'taste_rating', 'notes', 'sort_order'];

    protected function casts(): array
    {
        return ['taste_rating' => 'decimal:1', 'unit_price_idr' => 'integer', 'quantity' => 'decimal:2'];
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function foodType()
    {
        return $this->belongsTo(FoodType::class);
    }

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'visit_item_cuisine', 'visit_item_id', 'cuisine_id');
    }

    public function styles()
    {
        return $this->belongsToMany(CookingStyle::class, 'visit_item_cooking_style', 'visit_item_id', 'cooking_style_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'visit_item_ingredient', 'visit_item_id', 'ingredient_id')->withPivot('knowledge_source', 'notes');
    }

    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'visit_item_allergen', 'visit_item_id', 'allergen_id')->withPivot('exposure_status', 'evidence_source', 'notes');
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'visit_item_media', 'visit_item_id', 'media_id');
    }

    public function scopePublishedVisible($query)
    {
        return $query->whereHas('visit', fn ($q) => $q->publishedVisible());
    }
}
