<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Place;
use App\Models\Visit;
use App\Models\VisitItem;
use App\Support\ExploreState;
use App\Support\RatingSql;

class ExploreQuery
{
    public function build(ExploreState $state)
    {
        $tab = $state->get('tab');
        if ($tab === 'food') {
            $query = VisitItem::publishedVisible()->select('visit_item.*')->selectSub(Visit::select('visited_at')->whereColumn('visit.id', 'visit_item.visit_id'), 'visit_date')
                ->with(['visit.place', 'visit.coverMedia.variants', 'media.variants', 'allergens', 'cuisines', 'styles']);
            $query->whereHas('visit', fn ($q) => $this->visitFilters($q, $state));
            $this->itemFilters($query, $state);
            if ($state->get('q')) {
                $query->where(fn ($q) => $q->where('menu_name', 'like', '%'.$state->get('q').'%')->orWhereHas('foodType', fn ($f) => $f->where('name', 'like', '%'.$state->get('q').'%'))->orWhereHas('visit.place', fn ($p) => $p->where('name', 'like', '%'.$state->get('q').'%')));
            }
            $price = 'visit_item.unit_price_idr';
            $rating = 'visit_item.taste_rating';
            $date = 'visit_date';
            $id = 'visit_item.id';
        } elseif ($tab === 'place') {
            $query = Place::publishedVisible()->withRating()->with(['visits' => fn ($q) => $q->publishedVisible()->with(['coverMedia.variants'])->orderByDesc('visited_at')->orderByDesc('id')->limit(1)]);
            $query->whereHas('visits', function ($q) use ($state) {
                $q->publishedVisible();
                $this->visitFilters($q, $state);
                if ($this->hasItemFilters($state)) {
                    $q->whereHas('items', fn ($i) => $this->itemFilters($i, $state));
                }
            });
            if ($state->get('q')) {
                $query->where(fn ($q) => $q->where('name', 'like', '%'.$state->get('q').'%')->orWhere('city_name', 'like', '%'.$state->get('q').'%')->orWhereHas('visits', fn ($v) => $v->publishedVisible()->whereHas('items', fn ($i) => $i->where('menu_name', 'like', '%'.$state->get('q').'%'))));
            }
            $price = null;
            $rating = RatingSql::place();
            $date = 'latest_visit_at';
            $id = 'place.id';
        } else {
            $query = Visit::publishedVisible()->withRating()->with(['place', 'coverMedia.variants', 'recommendations', 'tags', 'items.foodType.category', 'items.styles']);
            $this->visitFilters($query, $state);
            if ($this->hasItemFilters($state)) {
                $query->whereHas('items', fn ($i) => $this->itemFilters($i, $state));
            }
            if ($state->get('q')) {
                $query->where(fn ($q) => $q->where('title', 'like', '%'.$state->get('q').'%')->orWhere('experience', 'like', '%'.$state->get('q').'%')->orWhereHas('place', fn ($p) => $p->where('name', 'like', '%'.$state->get('q').'%'))->orWhereHas('items', fn ($i) => $i->where('menu_name', 'like', '%'.$state->get('q').'%')));
            }
            $price = 'visit.total_spent_idr';
            $rating = RatingSql::overall();
            $date = 'visit.visited_at';
            $id = 'visit.id';
        }
        if ($price) {
            if ($state->get('price_min') !== null) {
                $query->where($price, '>=', $state->get('price_min'));
            }
            if ($state->get('price_max') !== null) {
                $query->where($price, '<=', $state->get('price_max'));
            }
        }
        if ($state->get('rating_min') !== null) {
            $query->whereRaw("{$rating} >= ?", [$state->get('rating_min')]);
        }
        $sort = $state->get('sort');
        if (str_starts_with($sort, 'rating_')) {
            $query->orderByRaw("{$rating} IS NULL")->orderByRaw($rating.($sort === 'rating_low' ? ' ASC' : ' DESC'));
        } elseif ($price && str_starts_with($sort, 'price_')) {
            $query->orderByRaw("{$price} IS NULL")->orderBy($price, $sort === 'price_low' ? 'asc' : 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('place.name');
        } else {
            $query->orderByRaw("{$date} IS NULL")->orderBy($date, $sort === 'oldest' ? 'asc' : 'desc');
        }

        return $query->orderByDesc($id);
    }

    private function visitFilters($query, ExploreState $state): void
    {
        if ($state->get('city')) {
            $query->whereHas('place', fn ($q) => $q->whereIn('city_slug', $state->get('city')));
        }
        foreach (['tag' => 'tags', 'recommendation' => 'recommendations'] as $key => $relation) {
            if ($state->get($key)) {
                $query->whereHas($relation, fn ($q) => $q->whereIn('slug', $state->get($key)));
            }
        }
    }

    private function hasItemFilters(ExploreState $state): bool
    {
        return collect(['category', 'food_type', 'cuisine', 'style', 'ingredient', 'avoid_allergen'])->contains(fn ($key) => filled($state->get($key)));
    }

    private function itemFilters($query, ExploreState $state): void
    {
        if ($state->get('category')) {
            $query->whereHas('foodType', fn ($q) => $q->whereIn('category_id', $this->descendants(Category::class, $state->get('category'))));
        }
        if ($state->get('food_type')) {
            $query->whereHas('foodType', fn ($q) => $q->whereIn('slug', $state->get('food_type')));
        }
        if ($state->get('cuisine')) {
            $query->whereHas('cuisines', fn ($q) => $q->whereIn('cuisine.id', $this->descendants(Cuisine::class, $state->get('cuisine'))));
        }
        foreach (['style' => 'styles', 'ingredient' => 'ingredients'] as $key => $relation) {
            if ($state->get($key)) {
                $query->whereHas($relation, fn ($q) => $q->whereIn('slug', $state->get($key)));
            }
        }
        if ($state->get('avoid_allergen')) {
            $codes = $state->get('avoid_allergen');
            if (in_array('cereals_gluten', $codes)) {
                $codes = array_unique([...$codes, 'wheat', 'rye', 'barley', 'oat']);
            }
            $query->whereDoesntHave('allergens', fn ($q) => $q->whereIn('code', $codes)->whereIn('exposure_status', ['contains', 'may_contain']));
        }
    }

    public function descendants(string $class, array $slugs): array
    {
        $ids = $class::whereIn('slug', $slugs)->pluck('id')->all();
        $frontier = $ids;
        for ($depth = 0; $depth < 10 && $frontier; $depth++) {
            $frontier = $class::whereIn('parent_id', $frontier)->whereNotIn('id', $ids)->pluck('id')->all();
            $ids = [...$ids, ...$frontier];
        }

        return $ids;
    }
}
