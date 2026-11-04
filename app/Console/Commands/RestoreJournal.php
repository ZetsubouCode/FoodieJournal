<?php

namespace App\Console\Commands;

use App\Services\JournalBackup;
use Illuminate\Console\Command;

class RestoreJournal extends Command
{
    protected $signature = 'foodie:restore {archive} {--database= : NEW foodie_restore_* target} {--media= : NEW media directory}';

    protected $description = 'Verify and restore a trusted Foodie archive only to NEW isolated targets';

    public function handle(JournalBackup $backup): int
    {
        if (! $this->option('database') || ! $this->option('media')) {
            $this->error('--database and --media are required; active targets are never inferred.');

            return self::FAILURE;
        }
        try {
            $manifest = $backup->restore($this->argument('archive'), $this->option('database'), $this->option('media'));
            $this->info('Isolated restore verified. Active database/media were not replaced.');
            $this->line(json_encode($manifest['tables']));

            return self::SUCCESS;
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
    }
}
