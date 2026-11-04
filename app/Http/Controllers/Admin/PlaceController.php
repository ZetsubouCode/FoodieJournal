<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceRequest;
use App\Models\Place;
use App\Services\Slugger;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:120']);

        return view('admin.place.index', ['places' => Place::when($request->q, fn ($q, $value) => $q->where('name', 'like', '%'.$value.'%'))->withCount('visits')->orderBy('name')->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.place.editor', ['place' => new Place]);
    }

    public function edit(Place $place)
    {
        return view('admin.place.editor', compact('place'));
    }

    public function store(PlaceRequest $request, Slugger $slugger)
    {
        return $this->save($request, new Place, $slugger);
    }

    public function update(PlaceRequest $request, Place $place, Slugger $slugger)
    {
        return $this->save($request, $place, $slugger);
    }

    private function save(PlaceRequest $request, Place $place, Slugger $slugger)
    {
        $data = $request->validated();
        $duplicate = Place::where('normalized_name', Str::lower(Str::squish($data['name'])))->when($data['city_name'] ?? null, fn ($q, $city) => $q->where('city_name', $city))->whereKeyNot($place->id ?? 0)->first();
        if ($duplicate && ! $request->boolean('confirm_duplicate')) {
            throw ValidationException::withMessages(['name' => 'Tempat serupa sudah ada: '.$duplicate->name.' ('.$duplicate->city_name.'). Pilih yang sudah ada atau konfirmasi tempat berbeda.']);
        }
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                DB::transaction(function () use ($place, $data, $slugger) {
                    $place->fill(collect($data)->except('slug')->all());
                    if (! $place->exists) {
                        $place->slug = $slugger->generate($place, $place->name);
                    }
                    $slugger->assign($place, $data['slug'] ?? null);
                    $place->save();
                });

                return $request->expectsJson() ? response()->json(['id' => $place->id, 'label' => $place->name.' · '.($place->city_name ?: 'Lokasi belum dicatat')], 201) : redirect('/admin/place')->with('success', 'Tempat disimpan.');
            } catch (UniqueConstraintViolationException $e) {
                if ($place->exists || ! empty($data['slug']) || $attempt === 4) {
                    throw ValidationException::withMessages(['slug' => 'Slug sudah digunakan.']);
                }
            }
        }
    }

    public function destroy(Place $place)
    {
        if ($place->visits()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['place' => 'Tempat mempunyai riwayat kunjungan dan tidak dapat dihapus.']);
        }
        $place->delete();

        return back()->with('success', 'Tempat dihapus.');
    }
}
