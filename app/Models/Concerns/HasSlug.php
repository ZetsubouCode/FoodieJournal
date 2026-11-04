<?php

namespace App\Models\Concerns;

use App\Services\Slugger;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (! $model->slug) {
                $model->slug = app(Slugger::class)->generate($model, $model->name ?? $model->title ?? 'kunjungan');
            }
        });
    }
}
