<?php

declare(strict_types=1);

namespace Indiyoin\Domain;

final class Normalizer
{
    public static function key(string $value): string
    {
        $value = trim($value);
        $value = function_exists('mb_strtoupper')
            ? mb_strtoupper($value, 'UTF-8')
            : strtoupper($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }

    public static function technicalKey(string $line, string $model): string
    {
        return self::key($line) . '|' . self::key($model);
    }
}
