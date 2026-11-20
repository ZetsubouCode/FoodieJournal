<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\RatingSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $table = 'visit';

    protected $guarded = ['id', 'user_id', 'creation_token', 'version'];

    protected function casts(): array
    {
        return ['visited_at' => 'immutable_datetime', 'total_spent_idr' => 'integer'];
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(VisitItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function media()
    {
        return $this->hasMany(Media::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverMedia()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'visit_tag', 'visit_id', 'tag_id');
    }

    public function recommendations()
    {
        return $this->belongsToMany(RecommendationLabel::class, 'visit_recommendation_label', 'visit_id', 'recommendation_label_id');
    }

    public function scopePublishedVisible(Builder $query): Builder
    {
        return $query->where('visit.status', 'published')->whereHas('place');
    }

    public function scopeWithFoodScore(Builder $query): Builder
    {
        return $query->addSelect('visit.*')
            ->selectRaw(RatingSql::visit().' AS food_score')
            ->selectRaw(RatingSql::visitItemCount().' AS rated_item_count');
    }

    public function getFoodScoreAttribute($value): ?float
    {
        if (array_key_exists('food_score', $this->attributes)) {
            return $value === null ? null : (float) $value;
        }

        return $this->items->avg('food_rating');
    }

    public function getRatedItemCountAttribute($value): int
    {
        return $value === null ? $this->items->whereNotNull('food_rating')->count() : (int) $value;
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: 'Kunjungan ke '.$this->place->name;
    }
}
