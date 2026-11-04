<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class Presentation
{
    public static function rating(?float $value): string
    {
        return $value === null ? 'Belum Dinilai' : number_format($value, 1, ',', '.');
    }

    public static function price(?int $value): string
    {
        return $value === null ? 'Harga belum dicatat' : 'Rp'.number_format($value, 0, ',', '.');
    }

    public static function localTime($value, string $format = 'd M Y, H:i'): string
    {
        return $value ? CarbonImmutable::parse($value, 'UTC')->setTimezone(config('foodie.timezone'))->locale('id')->translatedFormat($format) : 'Tanggal belum dicatat';
    }

    public static function utcFromLocal(?string $value): ?CarbonImmutable
    {
        return $value ? CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $value, config('foodie.timezone'))->utc() : null;
    }
}
