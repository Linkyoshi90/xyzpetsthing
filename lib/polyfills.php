<?php

// PHP 8+ compatibility helper for environments still on PHP 7.x.
if (!function_exists('str_contains')) {
    /**
     * Determine if a string contains a given substring.
     */
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
