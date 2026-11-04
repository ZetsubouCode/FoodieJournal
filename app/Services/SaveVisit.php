<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Place;
use App\Models\User;
use App\Models\Visit;
use App\Support\Presentation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveVisit
{
    public function __construct(private Slugger $slugger) {}

    public function handle(User $user, array $data, ?Visit $existing = null): Visit
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($user, $data, $existing) {
                    $visit = $existing ? Visit::lockForUpdate()->findOrFail($existing->id) : Visit::where('user_id', $user->id)->where('creation_token', $data['creation_token'])->first();
                    if (! $existing && $visit) {
                        return $visit;
                    }
                    if ($visit && $visit->version !== (int) $data['version']) {
                        throw ValidationException::withMessages(['version' => 'Catatan telah berubah di tab lain. Salin perubahan Anda, lalu muat ulang sebelum menyimpan.']);
                    }
                    $visit ??= new Visit;
                    if ($visit->exists && $visit->user_id !== $user->id) {
                        abort(403);
                    }
                    $new = ! $visit->exists;
                    $visit->fill(Arr::only($data, ['place_id', 'title', 'experience', 'service_review', 'atmosphere_review', 'cleanliness_review', 'value_review', 'conclusion', 'service_rating', 'atmosphere_rating', 'cleanliness_rating', 'value_rating', 'total_spent_idr']));
                    if (array_key_exists('visited_at', $data)) {
                        $visit->visited_at = Presentation::utcFromLocal($data['visited_at']);
                    }
                    if ($new) {
                        $visit->user_id = $user->id;
                        $visit->creation_token = $data['creation_token'];
                        $source = $data['title'] ?? Place::findOrFail($data['place_id'])->name.'-'.($data['visited_at'] ?? 'catatan');
                        $visit->slug = $this->slugger->generate($visit, $source);
                    }
                    $this->slugger->assign($visit, $data['slug'] ?? null);
                    $intent = $data['intent'] ?? 'save';
                    if ($intent !== 'save') {
                        $visit->status = $intent;
                    }
                    if ($new && $intent === 'save') {
                        $visit->status = 'draft';
                    }
                    $visit->version = $new ? 1 : $visit->version + 1;
                    $visit->save();
                    if (! empty($data['editor'])) {
                        $kept = [];
                        foreach ($data['items'] ?? [] as $index => $row) {
                            $item = ! empty($row['id']) ? $visit->items()->find($row['id']) : $visit->items()->make();
                            if (! $item) {
                                throw ValidationException::withMessages(["items.$index.id" => 'Hidangan bukan milik kunjungan ini.']);
                            }
                            $item->fill(Arr::only($row, ['food_type_id', 'menu_name', 'quantity', 'unit_price_idr', 'taste_rating', 'notes']));
                            $item->sort_order = $index;
                            $item->save();
                            $kept[] = $item->id;
                            foreach (['cuisines', 'styles'] as $relation) {
                                $item->$relation()->sync($row[$relation] ?? []);
                            }
                            $ingredients = [];
                            foreach ($row['ingredients'] ?? [] as $id) {
                                $ingredients[$id] = ['knowledge_source' => $row['ingredient_sources'][$id] ?? null];
                            }
                            $item->ingredients()->sync($ingredients);
                            $allergens = [];
                            foreach ($row['allergens'] ?? [] as $evidence) {
                                $allergens[$evidence['id']] = Arr::only($evidence, ['exposure_status', 'evidence_source', 'notes']);
                            }
                            $item->allergens()->sync($allergens);
                            $mediaIds = $row['media'] ?? [];
                            if (Media::where('visit_id', $visit->id)->whereIn('id', $mediaIds)->count() !== count($mediaIds)) {
                                throw ValidationException::withMessages(["items.$index.media" => 'Foto harus berasal dari kunjungan yang sama.']);
                            }
                            $item->media()->sync($mediaIds);
                        }
                        // Item deletion only detaches associations; original gallery files remain intact.
                        $removed = $visit->items()->whereNotIn('id', $kept)->get();
                        foreach ($removed as $item) {
                            foreach (['cuisines', 'styles', 'ingredients', 'allergens', 'media'] as $relation) {
                                $item->$relation()->detach();
                            } $item->delete();
                        }
                        $visit->tags()->sync($data['tags'] ?? []);
                        $visit->recommendations()->sync($data['recommendations'] ?? []);
                        $cover = $data['cover_media_id'] ?? null;
                        if ($cover && ! $visit->media()->whereKey($cover)->exists()) {
                            throw ValidationException::withMessages(['cover_media_id' => 'Cover harus berasal dari galeri kunjungan ini.']);
                        }
                        $visit->cover_media_id = $cover;
                        $visit->save();
                    }
                    if ($visit->status === 'published') {
                        $this->validatePublish($visit);
                    }

                    return $visit;
                }, 3);
            } catch (UniqueConstraintViolationException $e) {
                if ($existing || ! empty($data['slug']) || $attempt === 4) {
                    throw ValidationException::withMessages(['slug' => 'Data berbenturan dengan penyimpanan lain. Periksa slug, lalu simpan kembali.']);
                }
            }
        }
        throw new \LogicException('Unreachable');
    }

    public function validatePublish(Visit $visit): void
    {
        $errors = [];
        if (! $visit->visited_at) {
            $errors['visited_at'] = 'Isi tanggal dan waktu sebelum menerbitkan.';
        }
        $hasText = collect([$visit->experience, $visit->conclusion, $visit->service_review, $visit->atmosphere_review, $visit->cleanliness_review, $visit->value_review])->contains(fn ($text) => filled($text));
        if (! $hasText && ! $visit->items()->exists()) {
            $errors['experience'] = 'Tambahkan cerita atau setidaknya satu hidangan sebelum menerbitkan.';
        }
        if (! $visit->place()->exists()) {
            $errors['place_id'] = 'Tempat tidak tersedia.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
