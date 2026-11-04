<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaProcessor
{
    public function process(Media $media): bool
    {
        $lock = Cache::lock('media:'.$media->id, 180);
        if (! $lock->get()) {
            return false;
        }
        $image = null;
        try {
            $media->update(['processing_status' => 'processing', 'processing_error' => null]);
            $disk = Storage::disk('media');
            $source = $disk->get($media->original_path);
            if (hash('sha256', $source) !== $media->sha256) {
                throw new \RuntimeException('Checksum mismatch');
            }
            $image = @imagecreatefromstring($source);
            if (! $image) {
                throw new \RuntimeException('Decode failed');
            }
            $orientation = 1;
            if ($media->mime_type === 'image/jpeg' && function_exists('exif_read_data')) {
                $exif = @exif_read_data($disk->path($media->original_path));
                $orientation = (int) ($exif['Orientation'] ?? 1);
            }
            if (in_array($orientation, [2, 4, 5, 7])) {
                imageflip($image, in_array($orientation, [2, 5, 7]) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
            }
            $angle = match ($orientation) {
                3 => 180, 6, 7 => -90, 5, 8 => 90, default => 0
            };
            if ($angle) {
                $rotated = imagerotate($image, $angle, 0);
                imagedestroy($image);
                $image = $rotated;
            }
            $clean = app(ImageMetadata::class)->clean($source, $media->mime_type);
            if ($media->mime_type === 'image/jpeg' && $orientation >= 2 && $orientation <= 8) {
                // Minimal EXIF contains orientation only, never GPS/author/device metadata.
                $payload = "Exif\0\0II\x2a\0\x08\0\0\0\x01\0\x12\x01\x03\0\x01\0\0\0".pack('v', $orientation)."\0\0\0\0\0\0";
                $clean = substr($clean, 0, 2)."\xff\xe1".pack('n', strlen($payload) + 2).$payload.substr($clean, 2);
            }
            $this->putAtomic($media->uuid.'/viewer', $clean);
            foreach (['thumbnail' => 400, 'card' => 900, 'hero' => 1600] as $variant => $max) {
                $scale = min(1, $max / max(imagesx($image), imagesy($image)));
                $width = max(1, (int) round(imagesx($image) * $scale));
                $height = max(1, (int) round(imagesy($image) * $scale));
                $output = imagecreatetruecolor($width, $height);
                imagealphablending($output, false);
                imagesavealpha($output, true);
                imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
                ob_start();
                $webp = function_exists('imagewebp');
                $ok = $webp ? imagewebp($output, null, 82) : imagepng($output, null, 6);
                $bytes = ob_get_clean();
                imagedestroy($output);
                if (! $ok || ! $bytes) {
                    throw new \RuntimeException('Encoding failed');
                }
                $path = $media->uuid.'/'.$variant.($webp ? '.webp' : '.png');
                $this->putAtomic($path, $bytes);
                $media->variants()->updateOrCreate(['variant' => $variant], ['path' => $path, 'mime_type' => $webp ? 'image/webp' : 'image/png', 'width' => $width, 'height' => $height, 'bytes' => strlen($bytes), 'status' => 'ready']);
            }
            $media->update(['processing_status' => 'ready']);

            return true;
        } catch (\Throwable $error) {
            report($error);
            $media->update(['processing_status' => 'failed', 'processing_error' => 'Pemrosesan foto gagal. Original dipertahankan; coba proses ulang.']);

            return false;
        } finally {
            if ($image) {
                imagedestroy($image);
            } $lock->release();
        }
    }

    private function putAtomic(string $path, string $bytes): void
    {
        $disk = Storage::disk('media');
        $temp = $path.'.'.Str::random(12).'.tmp';
        try {
            $disk->put($temp, $bytes);
            $disk->move($temp, $path);
        } finally {
            if ($disk->exists($temp)) {
                $disk->delete($temp);
            }
        }
    }
}
