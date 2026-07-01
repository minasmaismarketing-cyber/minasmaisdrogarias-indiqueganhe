<?php

declare(strict_types=1);

/**
 * Acesso centralizado à configuração AppsFlyer.
 */
final class AppsFlyerConfig
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @return array<string, mixed> */
    public static function all(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $configFile = BASE_PATH . '/config/appsflyer.php';

        if (!is_file($configFile)) {
            self::$config = [
                'enabled' => false,
                'homologation' => false,
                'onelink_template' => '',
            ];

            return self::$config;
        }

        self::$config = require $configFile;

        return self::$config;
    }

    public static function isEnabled(): bool
    {
        return (bool) (self::all()['enabled'] ?? false);
    }

    public static function isHomologation(): bool
    {
        return (bool) (self::all()['homologation'] ?? false);
    }

    public static function isOneLinkConfigured(): bool
    {
        return trim((string) (self::all()['onelink_template'] ?? '')) !== '';
    }

    public static function isOneLinkActive(): bool
    {
        return self::isEnabled() && self::isOneLinkConfigured();
    }

    public static function reset(): void
    {
        self::$config = null;
    }
}
