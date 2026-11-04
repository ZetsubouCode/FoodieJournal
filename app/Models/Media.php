<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $guarded = ['id'];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function variants()
    {
        return $this->hasMany(MediaVariant::class);
    }

    public function items()
    {
        return $this->belongsToMany(VisitItem::class, 'visit_item_media', 'media_id', 'visit_item_id');
    }

    public function variant(string $name): ?MediaVariant
    {
        return $this->variants->first(fn ($variant) => $variant->variant === $name && $variant->status === 'ready');
    }
}
