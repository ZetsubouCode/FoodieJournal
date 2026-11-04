<?php

namespace App\Http\Requests;

use App\Rules\HalfRating;
use App\Rules\TextByteLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $visit = $this->route('visit');

        return (bool) $this->user()?->is_admin && (! $visit || $this->user()->can('update', $visit));
    }

    public function rules(): array
    {
        $rules = [
            'place_id' => ['required', 'integer', Rule::exists('place', 'id')->whereNull('deleted_at')],
            'visited_at' => 'nullable|date_format:Y-m-d\TH:i', 'title' => 'nullable|string|max:200',
            'slug' => 'nullable|string|max:180|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'intent' => 'nullable|in:save,draft,private,published', 'editor' => 'nullable|boolean',
            'creation_token' => $this->route('visit') ? 'nullable|uuid' : 'required|uuid',
            'version' => $this->route('visit') ? 'required|integer|min:1' : 'nullable|integer',
            'total_spent_idr' => 'nullable|integer|min:0|max:999999999999',
            'cover_media_id' => 'nullable|integer', 'items' => 'nullable|array|max:100',
            'items.*.id' => 'nullable|integer|distinct', 'items.*.menu_name' => 'required|string|max:200',
            'items.*.quantity' => 'nullable|numeric|min:0.01|max:999999.99',
            'items.*.unit_price_idr' => 'nullable|integer|min:0|max:999999999999',
            'items.*.food_type_id' => 'nullable|integer|exists:food_type,id',
            'items.*.taste_rating' => ['nullable', new HalfRating], 'items.*.notes' => 'nullable|string|max:10000',
            'items.*.ingredient_sources' => 'nullable|array|max:100', 'items.*.ingredient_sources.*' => 'nullable|string|max:500',
            'items.*.allergens' => 'nullable|array|max:30',
            'items.*.allergens.*.id' => 'required|integer|exists:allergen,id',
            'items.*.allergens.*.exposure_status' => 'required|in:contains,may_contain',
            'items.*.allergens.*.evidence_source' => 'required|string|max:500',
            'items.*.allergens.*.notes' => 'nullable|string|max:2000',
        ];
        foreach (['experience', 'service_review', 'atmosphere_review', 'cleanliness_review', 'value_review', 'conclusion'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:30000', new TextByteLimit];
        }
        foreach (['service', 'atmosphere', 'cleanliness', 'value'] as $field) {
            $rules[$field.'_rating'] = ['nullable', new HalfRating];
        }
        foreach (['cuisines' => 'cuisine', 'styles' => 'cooking_style', 'ingredients' => 'ingredient', 'media' => 'media'] as $field => $table) {
            $rules['items.*.'.$field] = 'nullable|array|max:100';
            $rules['items.*.'.$field.'.*'] = 'integer|exists:'.$table.',id';
        }
        foreach (['tags' => 'tag', 'recommendations' => 'recommendation_label'] as $field => $table) {
            $rules[$field] = 'nullable|array|max:40';
            $rules[$field.'.*'] = 'integer|distinct|exists:'.$table.',id';
        }

        return $rules;
    }

    public function after(): array
    {
        return [function ($validator) {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }
            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }
                foreach (['cuisines', 'styles', 'ingredients', 'media', 'allergens'] as $field) {
                    $values = $item[$field] ?? [];
                    if (! is_array($values)) {
                        continue;
                    }
                    if ($field === 'allergens') {
                        $values = array_column($values, 'id');
                    }
                    if (count($values) !== count(array_unique($values, SORT_REGULAR))) {
                        $validator->errors()->add("items.$index.$field", 'Pilihan yang sama tidak boleh diulang dalam satu hidangan.');
                    }
                }
            }
        }];
    }

    public function attributes(): array
    {
        return ['place_id' => 'tempat', 'visited_at' => 'tanggal kunjungan', 'items.*.menu_name' => 'nama hidangan', 'items.*.allergens.*.evidence_source' => 'sumber informasi alergen'];
    }
}
