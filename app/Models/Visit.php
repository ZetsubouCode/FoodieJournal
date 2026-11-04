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
        return ['visited_at' => 'immutable_datetime', 'total_spent_idr' => 'integer', 'service_rating' => 'decimal:1', 'atmosphere_rating' => 'decimal:1', 'cleanliness_rating' => 'decimal:1', 'value_rating' => 'decimal:1'];
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

    public function scopeWithRating(Builder $query): Builder
    {
        return $query->addSelect('visit.*')->selectRaw(RatingSql::overall().' AS overall_score');
    }

    public function getOverallRatingAttribute(): ?float
    {
        if (array_key_exists('overall_score', $this->attributes)) {
            return $this->overall_score === null ? null : (float) $this->overall_score;
        }
        $taste = $this->items->whereNotNull('taste_rating')->avg('taste_rating');
        $values = array_values(array_filter([$taste, $this->service_rating, $this->atmosphere_rating, $this->cleanliness_rating, $this->value_rating], fn ($v) => $v !== null));

        return $values ? array_sum($values) / count($values) : null;
    }

    public function getTasteRatingAttribute(): ?float
    {
        return $this->items->whereNotNull('taste_rating')->avg('taste_rating');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: 'Kunjungan ke '.$this->place->name;
    }
}
