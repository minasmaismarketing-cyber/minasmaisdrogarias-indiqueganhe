<?php

declare(strict_types=1);

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::TOKEN_KEY);
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get(self::TOKEN_KEY);

        if (!is_string($sessionToken) || !is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function validateRequest(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        return self::validate(is_string($token) ? $token : null);
    }
}
