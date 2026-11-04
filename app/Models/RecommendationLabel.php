<?php

namespace App\Models;

class RecommendationLabel extends Taxonomy
{
    protected $table = 'recommendation_label';

    public function visits()
    {
        return $this->belongsToMany(Visit::class, 'visit_recommendation_label', 'recommendation_label_id', 'visit_id');
    }
}
