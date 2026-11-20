<?php

namespace App\Support;

class RatingSql
{
    // Keep the SQL average at higher precision than MySQL's default for DECIMAL(2,1).
    private const AVERAGE = 'AVG(CAST(ri.food_rating AS DECIMAL(24,15)))';

    private const VISIT_ITEMS = 'FROM visit_item ri WHERE ri.visit_id = visit.id AND ri.food_rating IS NOT NULL';

    private const PLACE_VISITS = "WHERE rv.place_id = place.id AND rv.status = 'published' AND rv.deleted_at IS NULL AND place.deleted_at IS NULL";

    private const PLACE_ITEMS = 'FROM visit rv JOIN visit_item ri ON ri.visit_id = rv.id '.self::PLACE_VISITS.' AND ri.food_rating IS NOT NULL';

    public static function visit(): string
    {
        return '(SELECT '.self::AVERAGE.' '.self::VISIT_ITEMS.')';
    }

    public static function visitItemCount(): string
    {
        return '(SELECT COUNT(*) '.self::VISIT_ITEMS.')';
    }

    public static function place(): string
    {
        return '(SELECT '.self::AVERAGE.' '.self::PLACE_ITEMS.')';
    }

    public static function placeItemCount(): string
    {
        return '(SELECT COUNT(*) '.self::PLACE_ITEMS.')';
    }

    public static function placeVisitCount(): string
    {
        return '(SELECT COUNT(DISTINCT rv.id) '.self::PLACE_ITEMS.')';
    }

    public static function latestPlaceVisit(): string
    {
        return '(SELECT MAX(rv.visited_at) FROM visit rv '.self::PLACE_VISITS.')';
    }
}
