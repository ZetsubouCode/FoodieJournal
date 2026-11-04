<?php

namespace Tests\Feature;

use App\Models\Visit;
use App\Models\VisitItem;
use App\Services\JournalBackup;
use App\Services\MediaProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_and_media_restore_to_new_isolated_targets_with_checksums(): void
    {
        Storage::fake('media');
        $visit = Visit::factory()->published()->create(['total_spent_idr' => 60000]);
        $item = VisitItem::factory()->for($visit)->create(['menu_name' => 'Snapshot harga', 'unit_price_idr' => 25000]);
        $uuid = (string) Str::uuid();
        $photo = UploadedFile::fake()->image('restore-sample.jpg', 100, 80);
        $bytes = file_get_contents($photo->getRealPath());
        Storage::disk('media')->put($uuid.'/source', $bytes);
        $media = $visit->media()->create(['uuid' => $uuid, 'original_path' => $uuid.'/source', 'mime_type' => 'image/jpeg', 'original_bytes' => strlen($bytes), 'width' => 100, 'height' => 80, 'sha256' => hash('sha256', $bytes)]);
        app(MediaProcessor::class)->process($media);
        $visit->cover_media_id = $media->id;
        $visit->save();
        $item->media()->attach($media);
        $root = base_path('tools/.runtime/backup-test-'.Str::random(8));
        File::ensureDirectoryExists($root);
        $backup = app(JournalBackup::class);
        $manifest = $backup->create($root.'/journal.zip');
        $driver = DB::connection()->getDriverName();
        $target = $driver === 'sqlite' ? $root.'/foodie_restore_test.sqlite' : 'foodie_restore_'.strtolower(Str::random(12));
        $restored = $backup->restore($root.'/journal.zip', $target, $root.'/restored-media');
        $this->assertSame($manifest['tables'], $restored['tables']);
        $connection = DB::connection('foodie_restore');
        $this->assertSame('Snapshot harga', $connection->table('visit_item')->where('id', $item->id)->value('menu_name'));
        $this->assertEquals(25000, $connection->table('visit_item')->where('id', $item->id)->value('unit_price_idr'));
        $this->assertSame($bytes, file_get_contents($root.'/restored-media/'.$uuid.'/source'));
        $this->assertEquals($media->id, $connection->table('visit')->where('id', $visit->id)->value('cover_media_id'));
        $this->assertSame(3, $connection->table('media_variant')->where('media_id', $media->id)->count());
        $this->assertSame(1, $connection->table('visit_item_media')->where('media_id', $media->id)->count());
        $this->assertSame(1, Visit::count());
        $this->assertSame(60000, $visit->fresh()->total_spent_idr);
        $this->expectException(\RuntimeException::class);
        $backup->restore($root.'/journal.zip', $target, $root.'/restored-media');
    }

    public function test_restore_rejects_archive_tampering_before_creating_targets(): void
    {
        Storage::fake('media');
        $root = base_path('tools/.runtime/tamper-'.Str::random(8));
        File::ensureDirectoryExists($root);
        $backup = app(JournalBackup::class);
        $backup->create($root.'/journal.zip');
        $zip = new \ZipArchive;
        $zip->open($root.'/journal.zip');
        $zip->addFromString('database.json', '{}');
        $zip->close();
        try {
            $backup->restore($root.'/journal.zip', $root.'/foodie_restore_tampered.sqlite', $root.'/media');
            $this->fail('Tampered archive accepted');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('Checksum', $error->getMessage());
        }
        $this->assertFileDoesNotExist($root.'/foodie_restore_tampered.sqlite');
    }
}
