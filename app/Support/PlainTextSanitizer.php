<?php

namespace App\Support;

class PlainTextSanitizer
{
    public const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember',
        '_token',
    ];

    public static function clean(mixed $value, array $skipKeys = self::SENSITIVE_KEYS, string $currentKey = ''): mixed
    {
        if ($currentKey !== '' && in_array($currentKey, $skipKeys, true)) {
            return $value;
        }

        if (is_array($value)) {
            $clean = [];

            foreach ($value as $key => $item) {
                $nextKey = is_string($key) ? $key : $currentKey;
                $clean[$key] = self::clean($item, $skipKeys, $nextKey);
            }

            return $clean;
        }

        if (! is_string($value)) {
            return $value;
        }

        return self::cleanString($value);
    }

    public static function cleanString(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public static function isUnsafe(mixed $value, bool $allowUrls = false): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        if ($value !== strip_tags($value)) {
            return true;
        }

        if (preg_match('/<\s*\/?\s*[a-z]/i', $value) === 1) {
            return true;
        }

        if (preg_match('/javascript\s*:|vbscript\s*:|data\s*:/i', $value) === 1) {
            return true;
        }

        if (preg_match('/\bon[a-z]+\s*=/i', $value) === 1) {
            return true;
        }

        if (preg_match('/&lt;\s*\/?\s*[a-z]/i', $value) === 1) {
            return true;
        }

        if (! $allowUrls && preg_match('/(https?:\/\/|ftp:\/\/|www\.)/i', $value) === 1) {
            return true;
        }

        return false;
    }
}
