<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxonomyRequest;
use App\Models\Category;
use App\Models\FoodType;
use App\Services\Slugger;
use App\Support\Taxonomies;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaxonomyController extends Controller
{
    public function index(Request $request, string $kind = 'category')
    {
        $class = Taxonomies::model($kind);
        $request->validate(['q' => 'nullable|string|max:120']);
        $field = $kind === 'allergen' ? 'label_id' : 'name';

        return view('admin.taxonomy.index', ['kind' => $kind, 'label' => Taxonomies::label($kind), 'editing' => $request->filled('edit') ? $class::findOrFail((int) $request->edit) : new $class, 'terms' => $class::when($request->q, fn ($q, $value) => $q->where($field, 'like', '%'.$value.'%'))->orderBy($field)->paginate(30)->withQueryString()]);
    }

    public function store(TaxonomyRequest $request, string $kind, Slugger $slugger)
    {
        abort_if($kind === 'allergen', 403);

        return $this->save($request, $kind, new (Taxonomies::model($kind)), $slugger);
    }

    public function update(TaxonomyRequest $request, string $kind, int $id, Slugger $slugger)
    {
        return $this->save($request, $kind, Taxonomies::model($kind)::findOrFail($id), $slugger);
    }

    private function save(TaxonomyRequest $request, string $kind, $term, Slugger $slugger)
    {
        $data = $request->validated();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                DB::transaction(function () use ($kind, $data, $term, $slugger) {
                    if ($kind !== 'allergen') {
                        if ($term->newQuery()->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])->whereKeyNot($term->id ?? 0)->exists()) {
                            throw ValidationException::withMessages(['name' => 'Istilah ini sudah ada. Pilih hasil pencarian.']);
                        }
                        if (in_array($kind, ['category', 'food-type'])) {
                            $other = $kind === 'category' ? FoodType::class : Category::class;
                            if ($other::whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])->exists()) {
                                throw ValidationException::withMessages(['name' => 'Kategori umum dan jenis hidangan harus berbeda makna. Nama ini sudah dipakai di kelompok lain.']);
                            }
                        }
                        if (in_array($kind, ['category', 'cuisine'])) {
                            // Lock this small hierarchy before walking it so simultaneous edits cannot introduce cycles.
                            $nodes = $term->newQuery()->lockForUpdate()->get()->keyBy('id');
                            $seen = [$term->id];
                            $parent = $data['parent_id'] ?? null;
                            $depth = 1;
                            while ($parent) {
                                if (in_array($parent, $seen)) {
                                    throw ValidationException::withMessages(['parent_id' => 'Hierarki tidak boleh membentuk siklus.']);
                                }
                                $seen[] = $parent;
                                $parent = $nodes->get($parent)?->parent_id;
                                $depth++;
                            }
                            $subtreeDepth = function ($id) use (&$subtreeDepth, $nodes): int {
                                $children = $nodes->where('parent_id', $id);

                                return $children->isEmpty() ? 1 : 1 + $children->map(fn ($child) => $subtreeDepth($child->id))->max();
                            };
                            if ($depth + ($term->exists ? $subtreeDepth($term->id) - 1 : 0) > 3) {
                                throw ValidationException::withMessages(['parent_id' => 'Hierarki maksimal tiga tingkat.']);
                            }
                        }
                        $term->fill(collect($data)->except('slug')->all());
                        if (! $term->exists) {
                            $term->slug = $slugger->generate($term, $term->name);
                        }
                        $slugger->assign($term, $data['slug'] ?? null);
                    } else {
                        $term->is_active = $data['is_active'];
                    }
                    $term->save();
                });

                return $request->expectsJson() ? response()->json(['id' => $term->id, 'label' => $term->name], 201) : back()->with('success', 'Taksonomi disimpan.');
            } catch (UniqueConstraintViolationException $e) {
                if ($term->exists || ! empty($data['slug']) || $attempt === 4) {
                    throw ValidationException::withMessages(['slug' => 'Slug sudah digunakan.']);
                }
            }
        }
    }

    public function destroy(string $kind, int $id)
    {
        abort_if($kind === 'allergen', 403);
        $term = Taxonomies::model($kind)::findOrFail($id);
        $relations = match ($kind) {
            'category' => ['children', 'foodTypes'], 'cuisine' => ['children', 'items'], 'food-type', 'style' => ['items'], 'ingredient' => ['items', 'allergens'], 'tag', 'recommendation' => ['visits']
        };
        foreach ($relations as $relation) {
            if ($term->$relation()->exists()) {
                throw ValidationException::withMessages(['taxonomy' => 'Istilah masih dipakai. Nonaktifkan untuk mempertahankan sejarah.']);
            }
        }
        $term->delete();

        return back()->with('success', 'Istilah dihapus.');
    }
}
