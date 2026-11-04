<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Slugger
{
    public function generate(Model $model, string $source): string
    {
        $base = Str::limit(Str::slug($source) ?: 'catatan', 155, '');
        $slug = $base;
        $suffix = 2;
        while ($this->reserved($model, $slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function reserved(Model $model, string $slug): bool
    {
        $query = $model->newQueryWithoutScopes()->where('slug', $slug);
        if ($model->exists) {
            $query->whereKeyNot($model->getKey());
        }

        return $query->exists() || (in_array($model->getTable(), ['visit', 'place']) && DB::table('slug_redirect')->where('entity_type', $model->getTable())->where('old_slug', $slug)->exists());
    }

    public function assign(Model $model, ?string $slug): void
    {
        if ($slug === null || $slug === $model->slug) {
            return;
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || $this->reserved($model, $slug)) {
            throw ValidationException::withMessages(['slug' => 'Slug sudah digunakan atau tidak valid. Pilih slug lain.']);
        }
        if ($model->exists && in_array($model->getTable(), ['visit', 'place'])) {
            // Reserve old slugs even while private: they must not be reassigned to unrelated content.
            $wasPublic = $model->getTable() === 'visit' ? $model->getOriginal('status') === 'published' : $model->visits()->publishedVisible()->exists();
            DB::table('slug_redirect')->insertOrIgnore(['entity_type' => $model->getTable(), 'entity_id' => $model->id, 'old_slug' => $model->slug, 'allow_public_redirect' => $wasPublic]);
        }
        $model->slug = $slug;
    }
}
