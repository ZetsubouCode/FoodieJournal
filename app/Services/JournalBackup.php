<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class JournalBackup
{
    public function schemaHash(): string
    {
        $files = File::files(database_path('migrations'));
        $hashes = [];
        foreach ($files as $file) {
            $hashes[$file->getFilename()] = hash_file('sha256', $file->getRealPath());
        }
        ksort($hashes);

        return hash('sha256', json_encode($hashes));
    }

    public function create(string $destination): array
    {
        if (! app()->isDownForMaintenance() && ! app()->environment('testing')) {
            throw new RuntimeException('Jalankan php artisan down dan hentikan semua penulis/worker sebelum backup.');
        }
        if (file_exists($destination)) {
            throw new RuntimeException('Berkas tujuan sudah ada; backup tidak ditimpa.');
        }
        File::ensureDirectoryExists(dirname($destination));
        $resolved = realpath(dirname($destination)).DIRECTORY_SEPARATOR.basename($destination);
        foreach ([public_path(), Storage::disk('media')->path('')] as $forbidden) {
            if ($this->within($resolved, $forbidden)) {
                throw new RuntimeException('Backup harus berada di luar direktori publik dan media.');
            }
        }
        if (DB::table('media')->whereIn('processing_status', ['pending', 'processing'])->exists()) {
            throw new RuntimeException('Tunggu pemrosesan media selesai atau tandai ulang untuk retry sebelum backup.');
        }
        foreach (DB::table('media')->get() as $media) {
            $disk = Storage::disk('media');
            if (! $disk->exists($media->original_path) || ! hash_equals($media->sha256, hash_file('sha256', $disk->path($media->original_path)))) {
                throw new RuntimeException('Original hilang atau checksum berbeda. Pulihkan sumber sebelum membuat backup.');
            }
        }
        foreach (DB::table('media_variant')->where('status', 'ready')->get() as $variant) {
            if (! Storage::disk('media')->exists($variant->path)) {
                throw new RuntimeException('Varian siap tidak ditemukan. Proses ulang sebelum membuat backup.');
            }
        }
        $database = DB::transaction(function () {
            $data = [];
            foreach (Schema::getTableListing(null, false) as $table) {
                $data[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
            }

            return $data;
        });
        $json = json_encode($database, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        if (strlen($json) > 64 * 1024 * 1024) {
            throw new RuntimeException('Snapshot JSON melampaui batas 64 MB. Gunakan prosedur native dump pada dokumentasi.');
        }
        $manifest = ['format' => 1, 'created_at_utc' => now()->utc()->toIso8601String(), 'schema_hash' => $this->schemaHash(), 'driver' => DB::connection()->getDriverName(), 'tables' => array_map('count', $database), 'files' => ['database.json' => hash('sha256', $json)]];
        $zip = new ZipArchive;
        if ($zip->open($resolved, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new RuntimeException('Tidak dapat membuat arsip.');
        }
        try {
            $zip->addFromString('database.json', $json);
            foreach (Storage::disk('media')->allFiles() as $path) {
                if (str_ends_with($path, '.tmp')) {
                    continue;
                }
                $real = realpath(Storage::disk('media')->path($path));
                $root = realpath(Storage::disk('media')->path(''));
                if (! $real || ! str_starts_with(strtolower($real), strtolower($root.DIRECTORY_SEPARATOR))) {
                    throw new RuntimeException('Media path di luar root.');
                }
                $entry = 'media/'.$path;
                $manifest['files'][$entry] = hash_file('sha256', $real);
                $zip->addFile($real, $entry);
            }
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } finally {
            $zip->close();
        }
        $this->inspect($resolved);

        return $manifest;
    }

    public function inspect(string $archive): array
    {
        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('Arsip tidak dapat dibuka.');
        }
        try {
            $raw = $zip->getFromName('manifest.json');
            if (! $raw || strlen($raw) > 16 * 1024 * 1024) {
                throw new RuntimeException('Manifest tidak valid.');
            }
            $manifest = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? null) !== 1 || ($manifest['schema_hash'] ?? null) !== $this->schemaHash()) {
                throw new RuntimeException('Versi migration berbeda. Gunakan kode yang sama dengan backup.');
            }
            if ($zip->numFiles !== count($manifest['files']) + 1) {
                throw new RuntimeException('Isi arsip tidak sesuai manifest.');
            }
            foreach ($manifest['files'] as $path => $expected) {
                if ($path !== 'database.json' && ! preg_match('~^media/[a-f0-9-]{36}/[a-zA-Z0-9._-]+$~D', $path)) {
                    throw new RuntimeException('Path arsip tidak aman.');
                }
                $stream = $zip->getStream($path);
                if (! $stream) {
                    throw new RuntimeException('Berkas hilang: '.$path);
                }
                $context = hash_init('sha256');
                hash_update_stream($context, $stream);
                fclose($stream);
                if (! hash_equals($expected, hash_final($context))) {
                    throw new RuntimeException('Checksum tidak cocok: '.$path);
                }
            }
            $stat = $zip->statName('database.json');
            if (! $stat || $stat['size'] > 64 * 1024 * 1024) {
                throw new RuntimeException('Snapshot terlalu besar.');
            }

            return $manifest;
        } finally {
            $zip->close();
        }
    }

    public function restore(string $archive, string $databaseTarget, string $mediaTarget): array
    {
        $manifest = $this->inspect($archive);
        if (! preg_match('/^foodie_restore_[a-z0-9_]+$/D', basename($databaseTarget, '.sqlite'))) {
            throw new RuntimeException('Target DB harus baru dan bernama foodie_restore_* (SQLite diakhiri .sqlite).');
        }
        if (file_exists($mediaTarget)) {
            throw new RuntimeException('Direktori media tujuan harus belum ada.');
        }
        if (! is_dir(dirname($mediaTarget))) {
            throw new RuntimeException('Buat direktori induk restore terlebih dahulu, di luar media aktif dan direktori publik.');
        }
        $resolvedMedia = realpath(dirname($mediaTarget)).DIRECTORY_SEPARATOR.basename($mediaTarget);
        foreach ([public_path(), Storage::disk('media')->path('')] as $forbidden) {
            if ($this->within($resolvedMedia, $forbidden)) {
                throw new RuntimeException('Target restore harus di luar media aktif dan direktori publik.');
            }
        }
        $driver = DB::connection()->getDriverName();
        $config = config('database.connections.'.config('database.default'));
        if ($driver === 'sqlite') {
            if (! str_ends_with($databaseTarget, '.sqlite') || file_exists($databaseTarget) || (realpath($databaseTarget) !== false && realpath($databaseTarget) === realpath(DB::connection()->getDatabaseName()))) {
                throw new RuntimeException('Target SQLite harus berkas .sqlite baru.');
            }
            File::ensureDirectoryExists(dirname($databaseTarget));
            if ($this->within(realpath(dirname($databaseTarget)).DIRECTORY_SEPARATOR.basename($databaseTarget), public_path())) {
                throw new RuntimeException('Database restore tidak boleh di direktori publik.');
            }
            $file = fopen($databaseTarget, 'x');
            if (! $file) {
                throw new RuntimeException('Target DB tidak dapat dibuat.');
            } fclose($file);
        } elseif (in_array($driver, ['mysql', 'mariadb'])) {
            if (! preg_match('/^foodie_restore_[a-z0-9_]+$/D', $databaseTarget)) {
                throw new RuntimeException('Nama database restore tidak valid.');
            }
            if ($databaseTarget === DB::connection()->getDatabaseName() || DB::select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$databaseTarget])) {
                throw new RuntimeException('Target DB harus baru, bukan DB aktif.');
            }
            DB::statement('CREATE DATABASE `'.$databaseTarget.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } else {
            throw new RuntimeException('Driver restore tidak didukung.');
        }
        $config['database'] = $databaseTarget;
        $config['url'] = null;
        config(['database.connections.foodie_restore' => $config]);
        DB::purge('foodie_restore');
        $zip = new ZipArchive;
        $zip->open($archive, ZipArchive::RDONLY);
        try {
            Artisan::call('migrate', ['--database' => 'foodie_restore', '--force' => true]);
            $connection = DB::connection('foodie_restore');
            $schema = Schema::connection('foodie_restore');
            $data = json_decode($zip->getFromName('database.json'), true, 512, JSON_THROW_ON_ERROR);
            $tables = $schema->getTableListing(null, false);
            if (array_diff(array_keys($data), $tables) || array_diff($tables, array_keys($data))) {
                throw new RuntimeException('Tabel snapshot tidak sesuai schema.');
            }
            $schema->disableForeignKeyConstraints();
            try {
                $connection->transaction(function () use ($data, $connection) {
                    // Fresh target only. Migration records are replaced with the snapshot's exact records.
                    $connection->table('migration')->delete();
                    foreach ($data as $table => $rows) {
                        foreach (array_chunk($rows, 50) as $batch) {
                            $connection->table($table)->insert($batch);
                        }
                    }
                });
            } finally {
                $schema->enableForeignKeyConstraints();
            }
            foreach ($manifest['tables'] as $table => $count) {
                if ($connection->table($table)->count() !== $count) {
                    throw new RuntimeException('Jumlah record restore tidak cocok.');
                }
            }
            foreach ($tables as $table) {
                foreach ($schema->getForeignKeys($table) as $fk) {
                    $column = $fk['columns'][0];
                    $foreign = $fk['foreign_table'];
                    $foreignColumn = $fk['foreign_columns'][0];
                    if ($connection->table($table.' as a')->whereNotNull('a.'.$column)->whereNotExists(fn ($q) => $q->selectRaw('1')->from($foreign.' as b')->whereColumn('a.'.$column, 'b.'.$foreignColumn))->exists()) {
                        throw new RuntimeException('Relasi tidak valid setelah restore.');
                    }
                }
            }
            if ($connection->table('visit')->join('media', 'media.id', '=', 'visit.cover_media_id')->whereColumn('visit.id', '!=', 'media.visit_id')->exists()) {
                throw new RuntimeException('Cover lintas kunjungan tidak valid.');
            }
            if ($connection->table('visit_item_media as link')->join('visit_item as item', 'item.id', '=', 'link.visit_item_id')->join('media', 'media.id', '=', 'link.media_id')->whereColumn('item.visit_id', '!=', 'media.visit_id')->exists()) {
                throw new RuntimeException('Foto hidangan lintas kunjungan tidak valid.');
            }
            File::ensureDirectoryExists($mediaTarget);
            foreach ($manifest['files'] as $path => $hash) {
                if (str_starts_with($path, 'media/')) {
                    $target = rtrim($mediaTarget, '/\\').DIRECTORY_SEPARATOR.substr($path, 6);
                    File::ensureDirectoryExists(dirname($target));
                    $source = $zip->getStream($path);
                    $output = fopen($target, 'x');
                    stream_copy_to_stream($source, $output);
                    fclose($source);
                    fclose($output);
                    if (! hash_equals($hash, hash_file('sha256', $target))) {
                        throw new RuntimeException('Checksum media restore tidak cocok.');
                    }
                }
            }
            foreach ($connection->table('media')->get() as $media) {
                $path = rtrim($mediaTarget, '/\\').DIRECTORY_SEPARATOR.$media->original_path;
                if (! is_file($path) || ! hash_equals($media->sha256, hash_file('sha256', $path))) {
                    throw new RuntimeException('Original restore tidak sesuai record.');
                }
            }

            return $manifest;
        } finally {
            $zip->close();
            DB::disconnect('foodie_restore');
        }
    }

    private function within(string $path, string $root): bool
    {
        $normalize = fn ($value) => strtolower(str_replace('\\', '/', rtrim($value, '/\\'))).'/';

        return str_starts_with($normalize($path), $normalize(realpath($root) ?: $root));
    }
}
