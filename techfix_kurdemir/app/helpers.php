<?php

if (!function_exists('text_contains')) {
    function text_contains(?string $haystack, string $needle): bool {
        if (is_null($haystack)) return false;
        return str_contains(strtolower($haystack), strtolower($needle));
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 1): string {
        if ($bytes <= 0) return '0 B';
        $base = log($bytes, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}