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

    public function archivePhotoVisits()
    {
        return $this->hasMany(Visit::class)->publishedVisible()
            ->whereHas('media.variants', fn ($photo) => $photo->where('variant', 'card')->where('status', 'ready'))
            ->orderByDesc('visited_at')->orderByDesc('id')->limit(1);
    }

    public function scopePublishedVisible(Builder $query): Builder
    {
        return $query->whereHas('visits', fn ($q) => $q->publishedVisible());
    }

    protected function casts(): array
    {
        return ['food_score' => 'float', 'rated_item_count' => 'integer', 'rated_visit_count' => 'integer'];
    }

    public function scopeWithFoodScore(Builder $query): Builder
    {
        return $query->addSelect('place.*')
            ->selectRaw(RatingSql::place().' AS food_score')
            ->selectRaw(RatingSql::placeItemCount().' AS rated_item_count')
            ->selectRaw(RatingSql::placeVisitCount().' AS rated_visit_count')
            ->selectRaw(RatingSql::latestPlaceVisit().' AS latest_visit_at');
    }
}
