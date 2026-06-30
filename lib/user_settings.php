<?php

const USER_SETTINGS_NSFW_COOKIE = 'xyzpets_nsfw_mode';
const USER_SETTINGS_COOKIE_TTL = 31536000;

function user_settings_cookie_options(int $expires): array {
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function user_settings_nsfw_enabled(): bool {
    $value = $_COOKIE[USER_SETTINGS_NSFW_COOKIE] ?? '';

    return is_scalar($value) && (string)$value === '1';
}

function user_settings_set_nsfw_enabled(bool $enabled): void {
    $value = $enabled ? '1' : '0';

    setcookie(
        USER_SETTINGS_NSFW_COOKIE,
        $value,
        user_settings_cookie_options(time() + USER_SETTINGS_COOKIE_TTL)
    );

    $_COOKIE[USER_SETTINGS_NSFW_COOKIE] = $value;
}
