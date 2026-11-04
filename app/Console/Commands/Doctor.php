<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Doctor extends Command
{
    protected $signature = 'foodie:doctor';

    protected $description = 'Read-only runtime and database readiness checks without printing secrets';

    public function handle(): int
    {
        $rows = [['PHP', PHP_VERSION], ['Locale / display timezone', config('app.locale').' / '.config('foodie.timezone')], ['Storage timezone', config('app.timezone')], ['Image decoder', extension_loaded('gd') ? 'GD available' : 'MISSING GD'], ['WebP encoder', function_exists('imagewebp') ? 'available' : 'PNG fallback'], ['PHP upload / post limit', ini_get('upload_max_filesize').' / '.ini_get('post_max_size')], ['Media disk', 'private; no public symlink'], ['Queue', config('queue.default')]];
        $ok = extension_loaded('gd');
        try {
            DB::connection()->getPdo();
            $rows[] = ['Database', DB::connection()->getDriverName().' connected'];
        } catch (\Throwable $e) {
            $rows[] = ['Database', 'UNAVAILABLE; inspect private configuration'];
            $ok = false;
        }
        $this->table(['Check', 'Result'], $rows);
        $this->line('Set PHP upload_max_filesize >= 16M and post_max_size >= 20M for the full 15 MB upload allowance.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
