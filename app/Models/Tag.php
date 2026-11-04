<?php

namespace App\Models;

class Tag extends Taxonomy
{
    protected $table = 'tag';

    public function visits()
    {
        return $this->belongsToMany(Visit::class, 'visit_tag', 'tag_id', 'visit_id');
    }
}
