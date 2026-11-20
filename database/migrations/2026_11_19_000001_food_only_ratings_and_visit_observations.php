<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RETIRED_FIELDS = [
        'service_rating', 'atmosphere_rating', 'cleanliness_rating', 'value_rating',
        'service_review', 'atmosphere_review', 'cleanliness_review', 'value_review',
    ];

    public function up(): void
    {
        $hasLegacyNotes = DB::table('visit')->whereNotNull('service_review')
            ->orWhereNotNull('atmosphere_review')->orWhereNotNull('cleanliness_review')->orWhereNotNull('value_review')
            ->exists();

        if ($hasLegacyNotes && getenv('FOODIE_APPROVE_LEGACY_REVIEW_CLEANUP') !== '1') {
            throw new RuntimeException('Legacy Visit notes need owner review before schema cleanup. See docs/operations.md.');
        }

        Schema::table('visit_item', fn (Blueprint $table) => $table->renameColumn('taste_rating', 'food_rating'));
        Schema::table('visit', function (Blueprint $table) {
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();
        });
        // Follow the backup and legacy-note audit in docs/operations.md before running on owner data.
        Schema::table('visit', fn (Blueprint $table) => $table->dropColumn(self::RETIRED_FIELDS));
    }

    public function down(): void
    {
        // Rollback restores the column definitions. Their historical values require the pre-upgrade backup.
        Schema::table('visit', function (Blueprint $table) {
            foreach (self::RETIRED_FIELDS as $field) {
                if (str_ends_with($field, '_rating')) {
                    $table->decimal($field, 2, 1)->nullable();
                } else {
                    $table->text($field)->nullable();
                }
            }
        });
        Schema::table('visit', fn (Blueprint $table) => $table->dropColumn(['pros', 'cons']));
        Schema::table('visit_item', fn (Blueprint $table) => $table->renameColumn('food_rating', 'taste_rating'));
    }
};
