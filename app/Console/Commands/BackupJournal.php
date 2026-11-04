<?php

namespace App\Console\Commands;

use App\Services\JournalBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackupJournal extends Command
{
    protected $signature = 'foodie:backup {--destination= : New archive outside public/media directories}';

    protected $description = 'Create and checksum a consistent private database + media snapshot';

    public function handle(JournalBackup $backup): int
    {
        try {
            $target = $this->option('destination') ?: storage_path('backups/foodie-'.now()->utc()->format('Ymd-His').'-'.Str::random(8).'.zip');
            $manifest = $backup->create($target);
            $this->info('Backup verified: '.$target);
            $this->line(count($manifest['tables']).' tables, '.count($manifest['files']).' files.');

            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
