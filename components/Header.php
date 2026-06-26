<?php

declare(strict_types=1);

final class Header
{
    private const LOGO_FILE = 'Logo - Drogaria e Perfumaria.png';
    private const PLACEHOLDER = 'logo-placeholder.svg';

    /** @param array{userName?: string|null, showAuth?: bool, logoUrl?: string} $options */
    public static function render(array $options = []): void
    {
        $showAuth = $options['showAuth'] ?? true;
        $userName = $options['userName'] ?? null;
        $isGuest = !auth_check();
        $logoUrl = $options['logoUrl'] ?? brand_logo_url();
        $homeUrl = auth_check() ? url('/dashboard') : url('/');

        if ($userName === null && !$isGuest) {
            $user = Auth::user();
            $userName = is_array($user) ? (string) ($user['nome'] ?? '') : '';
        }

        require BASE_PATH . '/components/header.view.php';
    }

    public static function logoPath(): string
    {
        $primary = BASE_PATH . '/assets/images/' . self::LOGO_FILE;

        if (is_file($primary)) {
            return $primary;
        }

        return BASE_PATH . '/assets/images/' . self::PLACEHOLDER;
    }

    public static function usesPlaceholder(): bool
    {
        return !is_file(BASE_PATH . '/assets/images/' . self::LOGO_FILE);
    }
}
