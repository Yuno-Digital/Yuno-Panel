<?php

namespace App\Support;

/**
 * Small formatting helpers for the UI.
 */
class Format
{
    /**
     * Render a size given in megabytes as a human-readable string, scaling up
     * to GB / TB as appropriate. E.g. 30969 -> "30.2 GB", 465262 -> "454 GB",
     * 2097152 -> "2 TB".
     */
    public static function size(?int $mb): string
    {
        $mb = (int) $mb;

        if ($mb < 1024) {
            return $mb.' MB';
        }

        $gb = $mb / 1024;
        if ($gb < 1024) {
            return self::trim($gb).' GB';
        }

        return self::trim($gb / 1024).' TB';
    }

    /**
     * Format a number with one decimal place, dropping a trailing ".0".
     */
    private static function trim(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1), '0'), '.');
    }
}
