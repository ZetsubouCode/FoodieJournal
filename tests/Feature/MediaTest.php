<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Models\Visit;
use App\Services\ImageMetadata;
use App\Services\MediaProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('media');
    }

    private function ownerVisit(): Visit
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();
        $this->actingAs($user);

        return Visit::factory()->create(['user_id' => $user->id]);
    }

    public function test_upload_preserves_original_derives_and_revokes_every_variant(): void
    {
        $visit = $this->ownerVisit();
        $file = UploadedFile::fake()->image('photo.jpg', 1200, 800);
        $hash = hash_file('sha256', $file->getRealPath());
        $this->post('/admin/visit/'.$visit->id.'/media', ['photo' => $file, 'alt_text' => 'Hidangan di meja'])->assertSessionHasNoErrors();
        $media = Media::first();
        $this->assertSame('ready', $media->processing_status);
        $this->assertCount(3, $media->variants);
        $this->assertSame($hash, hash('sha256', Storage::disk('media')->get($media->original_path)));
        $this->assertSame(900, $media->variant('card')->width);
        $this->get('/media/'.$media->uuid.'/original')->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
        $this->post('/admin/logout');
        $this->get('/media/'.$media->uuid.'/original')->assertNotFound();
        $visit->update(['status' => 'published']);
        foreach (['original', 'card', 'thumbnail', 'hero'] as $variant) {
            $this->get('/media/'.$media->uuid.'/'.$variant)->assertOk();
        }
        $visit->update(['status' => 'private']);
        foreach (['original', 'card', 'thumbnail', 'hero'] as $variant) {
            $this->get('/media/'.$media->uuid.'/'.$variant)->assertNotFound();
        }
    }

    public function test_rejects_extension_signature_mismatch_svg_and_oversize(): void
    {
        $visit = $this->ownerVisit();
        $this->postJson('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->image('bad.png')->mimeType('image/jpeg')])->assertUnprocessable();
        $this->postJson('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->createWithContent('x.svg', '<svg onload="alert(1)"></svg>')])->assertUnprocessable();
        $this->postJson('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->create('huge.jpg', 16000, 'image/jpeg')])->assertUnprocessable();
        $this->assertSame(0, Media::count());
    }

    public function test_retry_is_idempotent_and_failure_keeps_source(): void
    {
        $visit = $this->ownerVisit();
        $this->post('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->image('good.jpg', 100, 100)]);
        $media = Media::first();
        $source = Storage::disk('media')->get($media->original_path);
        Storage::disk('media')->put($media->original_path, 'bad');
        $this->assertFalse(app(MediaProcessor::class)->process($media));
        $this->assertSame('failed', $media->fresh()->processing_status);
        Storage::disk('media')->assertExists($media->original_path);
        Storage::disk('media')->put($media->original_path, $source);
        $this->post('/admin/media/'.$media->id.'/retry')->assertSessionHasNoErrors();
        $this->post('/admin/media/'.$media->id.'/retry')->assertSessionHasNoErrors();
        $this->assertSame('ready', $media->fresh()->processing_status);
        $this->assertSame(3, $media->variants()->count());
    }

    public function test_cross_visit_cover_and_link_are_rejected_and_delete_retains_source(): void
    {
        $visit = $this->ownerVisit();
        $this->post('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->image('good.jpg')]);
        $media = Media::first();
        $other = Visit::factory()->create(['user_id' => $visit->user_id]);
        $base = ['place_id' => $other->place_id, 'version' => 1, 'intent' => 'draft', 'editor' => 1];
        $this->put('/admin/visit/'.$other->id, [...$base, 'cover_media_id' => $media->id])->assertSessionHasErrors('cover_media_id');
        $this->put('/admin/visit/'.$other->id, [...$base, 'items' => [['menu_name' => 'Wrong photo', 'media' => [$media->id]]]])->assertSessionHasErrors('items.0.media');
        $visit->update(['cover_media_id' => $media->id]);
        $this->delete('/admin/media/'.$media->id)->assertSessionHasNoErrors();
        $this->assertNull($visit->fresh()->cover_media_id);
        Storage::disk('media')->assertExists($media->original_path);
        $this->get('/media/'.$media->uuid.'/original')->assertNotFound();
    }

    public function test_jpeg_metadata_is_removed_without_recompression(): void
    {
        $file = UploadedFile::fake()->image('meta.jpg', 20, 10);
        $bytes = file_get_contents($file->getRealPath());
        $payload = 'Exif'.str_repeat('GPS-SECRET', 3);
        $withMetadata = substr($bytes, 0, 2)."\xff\xe1".pack('n', strlen($payload) + 2).$payload.substr($bytes, 2);
        $clean = app(ImageMetadata::class)->clean($withMetadata, 'image/jpeg');
        $this->assertSame(substr($bytes, strpos($bytes, "\xff\xda")), substr($clean, strpos($clean, "\xff\xda")));
        $this->assertSame(getimagesizefromstring($bytes), getimagesizefromstring($clean));
        $this->assertStringNotContainsString('GPS-SECRET', $clean);
    }

    public function test_oversized_pixel_headers_are_rejected_before_decoding(): void
    {
        $visit = $this->ownerVisit();
        $photo = UploadedFile::fake()->image('pixels.png', 16, 16);
        $bytes = substr_replace(file_get_contents($photo->getRealPath()), pack('NN', 50000, 50000), 16, 8);
        $this->postJson('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->createWithContent('pixels.png', $bytes)])->assertUnprocessable()->assertJsonValidationErrors('photo');
        $this->assertSame(0, Media::count());
    }

    public function test_mirrored_exif_orientations_are_transposed_in_the_correct_direction(): void
    {
        $visit = $this->ownerVisit();
        $image = imagecreatetruecolor(80, 40);
        imagefilledrectangle($image, 0, 0, 39, 19, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 40, 0, 79, 19, imagecolorallocate($image, 0, 255, 0));
        imagefilledrectangle($image, 0, 20, 39, 39, imagecolorallocate($image, 0, 0, 255));
        imagefilledrectangle($image, 40, 20, 79, 39, imagecolorallocate($image, 255, 255, 0));
        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        foreach ([5 => [255, 0, 0], 7 => [255, 255, 0]] as $orientation => $expected) {
            $payload = "Exif\0\0II\x2a\0\x08\0\0\0\x01\0\x12\x01\x03\0\x01\0\0\0".pack('v', $orientation)."\0\0\0\0\0\0";
            $source = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($payload) + 2).$payload.substr($jpeg, 2);
            $this->post('/admin/visit/'.$visit->id.'/media', ['photo' => UploadedFile::fake()->createWithContent('orientation.jpg', $source)])->assertSessionHasNoErrors();
            $media = Media::latest('id')->first();
            $variant = $media->variant('thumbnail');
            $this->assertSame(40, $variant->width);
            $this->assertSame(80, $variant->height);
            $decoded = imagecreatefromstring(Storage::disk('media')->get($variant->path));
            $rgb = imagecolorsforindex($decoded, imagecolorat($decoded, 10, 10));
            imagedestroy($decoded);
            foreach (['red', 'green', 'blue'] as $index => $channel) {
                $this->assertEqualsWithDelta($expected[$index], $rgb[$channel], 20);
            }
        }
    }
}
