<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaRequest;
use App\Models\Media;
use App\Models\Visit;
use App\Services\MediaProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    public function show(Request $request, string $uuid, string $variant)
    {
        abort_unless(in_array($variant, ['original', 'thumbnail', 'card', 'hero']), 404);
        $media = Media::where('uuid', $uuid)->whereHas('visit', function ($q) use ($request) {
            $q->where(fn ($v) => $v->publishedVisible()->when($request->user()?->is_admin, fn ($v) => $v->orWhere('user_id', $request->user()->id)));
        })->firstOrFail();
        if ($variant === 'original') {
            $path = $media->uuid.'/viewer';
            $mime = $media->mime_type;
        } else {
            $asset = $media->variants()->where('variant', $variant)->where('status', 'ready')->firstOrFail();
            $path = $asset->path;
            $mime = $asset->mime_type;
        }
        $disk = Storage::disk('media');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $uuid.($variant === 'original' ? '-original' : '-'.$variant), ['Content-Type' => $mime, 'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => 'inline']);
    }

    public function store(MediaRequest $request, Visit $visit, MediaProcessor $processor)
    {
        $file = $request->file('photo');
        $uuid = (string) Str::uuid();
        $path = $uuid.'/source';
        $disk = Storage::disk('media');
        $disk->putFileAs($uuid, $file, 'source');
        try {
            $media = DB::transaction(function () use ($request, $visit, $file, $uuid, $path) {
                Visit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
                if ($visit->media()->count() >= config('foodie.max_media_per_visit')) {
                    throw ValidationException::withMessages(['photo' => 'Batas 40 foto per kunjungan tercapai.']);
                }
                [$width, $height] = getimagesize($file->getRealPath());

                return $visit->media()->create(['uuid' => $uuid, 'original_path' => $path, 'mime_type' => $file->getMimeType(), 'original_bytes' => $file->getSize(), 'width' => $width, 'height' => $height, 'sha256' => hash_file('sha256', $file->getRealPath()), 'alt_text' => $request->validated('alt_text'), 'sort_order' => $visit->media()->count()]);
            });
        } catch (\Throwable $e) {
            $disk->delete($path);
            throw $e;
        }
        $ok = $processor->process($media);

        return redirect('/admin/visit/'.$visit->id.'/edit#foto')->with('success', $ok ? 'Foto dan varian tersimpan.' : 'Original tersimpan. Varian perlu diproses ulang; lihat pesan di galeri.');
    }

    public function retry(Media $media, MediaProcessor $processor)
    {
        Gate::authorize('update', $media->visit);
        $processor->process($media);

        return back()->with('success', 'Pemrosesan ulang selesai. Periksa status foto.');
    }

    public function update(Request $request, Media $media)
    {
        Gate::authorize('update', $media->visit);
        $data = $request->validate(['media_alt' => 'required|array|max:1', 'media_alt.'.$media->id => 'present|nullable|string|max:300']);
        $media->update(['alt_text' => $data['media_alt'][$media->id]]);

        return back()->with('success', 'Deskripsi foto disimpan.');
    }

    public function destroy(Media $media)
    {
        Gate::authorize('update', $media->visit);
        DB::transaction(function () use ($media) {
            $visit = Visit::whereKey($media->visit_id)->lockForUpdate()->firstOrFail();
            if ($visit->cover_media_id === $media->id) {
                $visit->cover_media_id = null;
                $visit->version++;
                $visit->save();
            }
            $media->items()->detach();
            $media->delete();
        });

        return back()->with('success', 'Foto dilepas dari galeri. Original tetap berada dalam arsip privat.');
    }
}
