<?php

declare(strict_types=1);

class AuthMiddleware
{
    public static function requireAuth(): void
    {
        Auth::attemptFromRememberCookie();

        if (!Auth::check()) {
            Session::flash('error', 'Faça login para continuar.');
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public static function requireGuest(): void
    {
        Auth::attemptFromRememberCookie();

        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
