<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('visit')) ?? false;
    }

    public function rules(): array
    {
        return ['photo' => ['required', 'file', 'max:'.config('foodie.max_upload_kb'), 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'], 'alt_text' => 'nullable|string|max:300'];
    }

    public function after(): array
    {
        return [function ($validator) {
            $file = $this->file('photo');
            if (! $file || ! $file->isValid() || $validator->errors()->has('photo')) {
                return;
            }
            $info = @getimagesize($file->getRealPath());
            $expected = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][strtolower($file->getClientOriginalExtension())] ?? null;
            if (! $info || $info['mime'] !== $expected || $file->getMimeType() !== $expected) {
                $validator->errors()->add('photo', 'Isi foto dan ekstensi tidak cocok.');

                return;
            }
            $limit = ini_get('memory_limit');
            $bytes = (int) $limit * match (strtolower(substr($limit, -1))) {
                'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1
            };
            $pixelLimit = min(config('foodie.max_image_pixels'), $bytes > 0 ? max(0, (int) (($bytes - memory_get_usage(true) - 33554432) / 8)) : config('foodie.max_image_pixels'));
            if ($info[0] < 1 || $info[1] < 1 || $info[0] > 10000 || $info[1] > 10000 || $pixelLimit < $info[0] * $info[1]) {
                $validator->errors()->add('photo', 'Dimensi foto melebihi kapasitas pemrosesan. Pilih foto lebih kecil (maksimum 24 megapiksel).');
            }
        }];
    }
}
