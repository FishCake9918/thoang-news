<?php

namespace App\Core;

class TextHelper
{
    public static function short(string $text, int $limit = 120): string
    {
        $text = trim(strip_tags($text));

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
        }

        return strlen($text) > $limit
            ? substr($text, 0, $limit - 3) . '...'
            : $text;
    }
}
