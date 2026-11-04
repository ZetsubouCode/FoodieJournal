<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaVariant extends Model
{
    protected $table = 'media_variant';

    protected $guarded = ['id'];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
