<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:160', 'slug' => 'nullable|string|max:180|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'address' => 'nullable|string|max:2000', 'city_name' => 'nullable|string|max:120', 'province_name' => 'nullable|string|max:120', 'maps_url' => 'nullable|url:http,https|max:2048', 'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude', 'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude'];
    }
}
