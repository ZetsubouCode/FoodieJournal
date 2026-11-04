<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitRequest;
use App\Models\Place;
use App\Models\Visit;
use App\Services\SaveVisit;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['status' => 'nullable|in:draft,private,published', 'place_id' => 'nullable|integer', 'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
        $query = Visit::where('user_id', $request->user()->id)->with('place')->withRating();
        foreach (['status', 'place_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['from'])) {
            $query->where('visited_at', '>=', CarbonImmutable::parse($filters['from'], config('foodie.timezone'))->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('visited_at', '<', CarbonImmutable::parse($filters['to'], config('foodie.timezone'))->addDay()->utc());
        }

        return view('admin.visit.index', ['visits' => $query->orderByDesc('visited_at')->orderByDesc('id')->paginate(20)->withQueryString(), 'places' => Place::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.visit.editor', ['visit' => new Visit, 'quick' => true, 'rows' => []]);
    }

    public function store(VisitRequest $request, SaveVisit $save)
    {
        $visit = $save->handle($request->user(), $request->validated());

        return redirect('/admin/visit/'.$visit->id.'/edit')->with('success', 'Draft disimpan. Lengkapi kapan saja.');
    }

    public function edit(Visit $visit)
    {
        Gate::authorize('update', $visit);
        $visit->load(['place', 'items.foodType', 'items.cuisines', 'items.styles', 'items.ingredients', 'items.allergens', 'items.media', 'media.variants', 'tags', 'recommendations']);
        $rows = $visit->items->map(function ($item) {
            $row = $item->only(['id', 'menu_name', 'food_type_id', 'quantity', 'unit_price_idr', 'taste_rating', 'notes']);
            foreach (['cuisines', 'styles', 'ingredients'] as $relation) {
                $row[$relation] = $item->$relation->map(fn ($term) => ['id' => $term->id, 'label' => $term->name])->all();
            }
            $row['media'] = $item->media->modelKeys();
            $row['food_type_option'] = $item->foodType ? ['id' => $item->foodType->id, 'label' => $item->foodType->name] : null;
            $row['ingredient_sources'] = $item->ingredients->mapWithKeys(fn ($term) => [$term->id => $term->pivot->knowledge_source])->all();
            $row['allergens'] = $item->allergens->map(fn ($term) => ['id' => $term->id, 'option' => ['id' => $term->id, 'label' => $term->label_id], 'exposure_status' => $term->pivot->exposure_status, 'evidence_source' => $term->pivot->evidence_source, 'notes' => $term->pivot->notes])->all();

            return $row;
        })->all();

        return view('admin.visit.editor', ['visit' => $visit, 'quick' => false, 'rows' => $rows]);
    }

    public function update(VisitRequest $request, Visit $visit, SaveVisit $save)
    {
        $save->handle($request->user(), $request->validated(), $visit);

        return back()->with('success', 'Kunjungan disimpan.');
    }

    public function preview(Visit $visit)
    {
        Gate::authorize('preview', $visit);
        $visit->load(['place', 'items.foodType.category', 'items.cuisines', 'items.styles', 'items.ingredients', 'items.allergens', 'items.media.variants', 'media.variants', 'coverMedia.variants', 'tags', 'recommendations']);

        return response()->view('public.visit', ['visit' => $visit, 'preview' => true, 'related' => collect()])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function destroy(Visit $visit)
    {
        Gate::authorize('delete', $visit);
        $visit->delete();

        return redirect('/admin/visit')->with('success', 'Kunjungan dihapus dari jurnal. Data dan foto disimpan untuk pemulihan.');
    }
}
