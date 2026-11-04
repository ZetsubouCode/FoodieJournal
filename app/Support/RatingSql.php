<?php

namespace App\Support;

class RatingSql
{
    // Only internal, fixed table aliases may be passed; no request input enters SQL.
    public static function overall(string $alias = 'visit'): string
    {
        $taste = "(SELECT AVG(ri.taste_rating) FROM visit_item ri WHERE ri.visit_id = {$alias}.id)";
        $fields = [$taste, ...array_map(fn ($name) => "{$alias}.{$name}_rating", ['service', 'atmosphere', 'cleanliness', 'value'])];
        $sum = implode(' + ', array_map(fn ($field) => "COALESCE({$field}, 0)", $fields));
        $count = implode(' + ', array_map(fn ($field) => "CASE WHEN {$field} IS NOT NULL THEN 1 ELSE 0 END", $fields));

        return "(1.0 * ({$sum}) / NULLIF(({$count}), 0))";
    }

    public static function place(): string
    {
        $overall = self::overall('rv');

        return "(SELECT AVG({$overall}) FROM visit rv WHERE rv.place_id = place.id AND rv.status = 'published' AND rv.deleted_at IS NULL)";
    }
}
