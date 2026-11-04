<?php

namespace App\Http\Requests;

use App\Support\Taxonomies;
use Illuminate\Foundation\Http\FormRequest;

class TaxonomyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin && ! ($this->route('kind') === 'allergen' && $this->isMethod('POST'));
    }

    public function rules(): array
    {
        $kind = $this->route('kind');
        Taxonomies::model($kind);
        if ($kind === 'allergen') {
            return ['is_active' => 'required|boolean'];
        }
        $rules = ['name' => 'required|string|max:160', 'slug' => 'nullable|string|max:180|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'is_active' => 'sometimes|boolean'];
        if (in_array($kind, ['category', 'cuisine'])) {
            $rules['parent_id'] = 'nullable|integer|exists:'.$kind.',id';
        }
        if ($kind === 'food-type') {
            $rules['category_id'] = 'nullable|integer|exists:category,id';
        }
        if ($kind === 'cuisine') {
            $rules['classification'] = 'required|in:region,tradition,editorial';
        }

        return $rules;
    }
}
