<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\RatingSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Place extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $table = 'place';

    protected $fillable = ['name', 'slug', 'address', 'city_name', 'province_name', 'maps_url', 'latitude', 'longitude'];

    protected static function booted(): void
    {
        static::saving(function ($place) {
            $place->normalized_name = Str::lower(Str::squish($place->name));
            $place->city_slug = $place->city_name ? Str::slug($place->city_name) : null;
        });
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function scopePublishedVisible(Builder $query): Builder
    {
        return $query->whereHas('visits', fn ($q) => $q->publishedVisible());
    }

    public function scopeWithRating(Builder $query): Builder
    {
        $overall = RatingSql::overall('rv');

        return $query->addSelect('place.*')->selectRaw(RatingSql::place().' AS rating_score')
            ->selectRaw("(SELECT COUNT(*) FROM visit rv WHERE rv.place_id = place.id AND rv.status = 'published' AND rv.deleted_at IS NULL AND {$overall} IS NOT NULL) AS rated_visit_count")
            ->selectRaw("(SELECT MAX(rv.visited_at) FROM visit rv WHERE rv.place_id = place.id AND rv.status = 'published' AND rv.deleted_at IS NULL) AS latest_visit_at");
    }
}
