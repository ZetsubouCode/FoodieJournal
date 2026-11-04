<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Support\Taxonomies;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function __invoke(Request $request, string $kind)
    {
        $data = $request->validate(['q' => 'nullable|string|max:120', 'ids' => 'nullable|array|max:100', 'ids.*' => 'integer']);
        $q = $data['q'] ?? '';
        $ids = $data['ids'] ?? [];
        if ($kind === 'place') {
            $results = Place::when($ids, fn ($query) => $query->whereIn('id', $ids))->when($q !== '', fn ($query) => $query->where(fn ($s) => $s->where('name', 'like', '%'.$q.'%')->orWhere('city_name', 'like', '%'.$q.'%')))->orderByDesc('updated_at')->limit($ids ? 100 : 20)->get()->map(fn ($place) => ['id' => $place->id, 'label' => $place->name.' · '.($place->city_name ?: 'Lokasi belum dicatat')]);
        } else {
            $class = Taxonomies::model($kind);
            $field = $kind === 'allergen' ? 'label_id' : 'name';
            $results = $class::when($ids, fn ($query) => $query->whereIn('id', $ids), fn ($query) => $query->where('is_active', true))->when($q !== '', fn ($query) => $query->where($field, 'like', '%'.$q.'%'))->orderBy($field)->limit($ids ? 100 : 20)->get()->map(fn ($term) => ['id' => $term->id, 'label' => $term->$field]);
        }

        return response()->json(['results' => $results]);
    }
}
