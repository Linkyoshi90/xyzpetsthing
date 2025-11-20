<?php
if (!isset($GLOBALS['app_errors']) || !is_array($GLOBALS['app_errors'])) {
    $GLOBALS['app_errors'] = [];
}

function app_add_error(string $message): void
{
    if ($message === '') {
        return;
    }

    if (!isset($GLOBALS['app_errors']) || !is_array($GLOBALS['app_errors'])) {
        $GLOBALS['app_errors'] = [];
    }

    $GLOBALS['app_errors'][] = $message;
}

function app_get_errors(): array
{
    if (!isset($GLOBALS['app_errors']) || !is_array($GLOBALS['app_errors'])) {
        return [];
    }

    $errors = array_filter(array_map('strval', $GLOBALS['app_errors']));
    return array_values(array_unique($errors));
}

function app_add_error_from_exception(Throwable $e, string $prefix = ''): void
{
    $message = trim($prefix.' '.$e->getMessage());
    app_add_error($message);
}