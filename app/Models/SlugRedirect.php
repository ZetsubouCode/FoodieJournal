<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    protected $table = 'slug_redirect';

    public $timestamps = false;

    protected $guarded = ['id'];
}
