<?php

declare(strict_types=1);

function input_string($value, int $maxLength = 0, bool $stripTags = true): string
{
    if (!is_scalar($value)) {
        return '';
    }

    $string = (string) $value;
    $string = str_replace("\0", '', $string);
    if ($stripTags) {
        $string = strip_tags($string);
    }
    $string = trim($string);
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string) ?? '';

    if ($maxLength > 0 && mb_strlen($string) > $maxLength) {
        $string = mb_substr($string, 0, $maxLength);
    }

    return $string;
}

function input_email($value): string
{
    $email = input_string($value, 254, true);
    if ($email === '') {
        return '';
    }

    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function input_password($value, int $maxLength = 1024): string
{
    if (!is_scalar($value)) {
        return '';
    }

    $password = str_replace("\0", '', (string) $value);
    if ($maxLength > 0 && mb_strlen($password) > $maxLength) {
        $password = mb_substr($password, 0, $maxLength);
    }

    return $password;
}

function input_int($value, ?int $min = null, ?int $max = null): int
{
    if (!is_scalar($value)) {
        return 0;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    if ($filtered === false) {
        return 0;
    }

    $intVal = (int) $filtered;
    if ($min !== null && $intVal < $min) {
        return 0;
    }
    if ($max !== null && $intVal > $max) {
        return 0;
    }

    return $intVal;
}

function input_float($value, ?float $min = null, ?float $max = null): float
{
    if (!is_scalar($value)) {
        return 0.0;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($filtered === false) {
        return 0.0;
    }

    $floatVal = (float) $filtered;
    if ($min !== null && $floatVal < $min) {
        return 0.0;
    }
    if ($max !== null && $floatVal > $max) {
        return 0.0;
    }

    return $floatVal;
}
