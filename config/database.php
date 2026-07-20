<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    /** @return array{host: string, port: string, name: string, user: string, pass: string} */
    private static function envConfig(): array
    {
        return [
            'host' => trim((string) Env::get('DB_HOST', '')),
            'port' => trim((string) Env::get('DB_PORT', '')),
            'name' => trim((string) (Env::get('DB_DATABASE') ?? Env::get('DB_NAME') ?? '')),
            'user' => trim((string) (Env::get('DB_USERNAME') ?? Env::get('DB_USER') ?? '')),
            'pass' => (string) (Env::get('DB_PASSWORD') ?? Env::get('DB_PASS') ?? ''),
        ];
    }

    public static function resetConnection(): void
    {
        self::$connection = null;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $cfg = self::envConfig();
        self::assertConfig($cfg);

        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";

        self::$connection = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::applySessionTimezone(self::$connection);

        return self::$connection;
    }

    /**
     * Alinha NOW()/TIMESTAMP da sessão MySQL ao fuso da aplicação (tempo real BR).
     * Usa offset numérico (ex.: -03:00) — compatível com Hostinger sem timezone tables.
     */
    public static function applySessionTimezone(PDO $pdo): void
    {
        $tzName = (string) Env::get('APP_TIMEZONE', 'America/Sao_Paulo');
        try {
            $tz = new DateTimeZone($tzName);
        } catch (Exception $e) {
            $tz = new DateTimeZone('America/Sao_Paulo');
        }

        $offset = (new DateTimeImmutable('now', $tz))->format('P');
        $pdo->exec('SET time_zone = ' . $pdo->quote($offset));
    }

    public static function isConnected(): bool
    {
        return self::connectionTest()['connected'];
    }

    /** @return array{host: string, db: string, user: string, status: string, connected: bool} */
    public static function connectionTest(): array
    {
        self::resetConnection();

        $cfg = self::envConfig();
        $result = [
            'host' => $cfg['host'] . ($cfg['port'] !== '' ? ':' . $cfg['port'] : ''),
            'db' => $cfg['name'],
            'user' => $cfg['user'],
            'status' => '',
            'connected' => false,
        ];

        $missing = [];
        if ($cfg['host'] === '') {
            $missing[] = 'DB_HOST';
        }
        if ($cfg['port'] === '') {
            $missing[] = 'DB_PORT';
        }
        if ($cfg['name'] === '') {
            $missing[] = 'DB_DATABASE';
        }
        if ($cfg['user'] === '') {
            $missing[] = 'DB_USERNAME';
        }
        if (!Env::has('DB_PASSWORD') && !Env::has('DB_PASS')) {
            $missing[] = 'DB_PASSWORD';
        }

        if ($missing !== []) {
            $result['status'] = 'Variável ausente no .env: ' . implode(', ', $missing);
            return $result;
        }

        try {
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
            self::$connection = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::applySessionTimezone(self::$connection);
            self::$connection->query('SELECT 1');
            $result['status'] = 'Connection OK';
            $result['connected'] = true;
        } catch (PDOException $e) {
            self::resetConnection();
            $result['status'] = $e->getMessage();
        }

        return $result;
    }

    /** @param array{host: string, port: string, name: string, user: string, pass: string} $cfg */
    private static function assertConfig(array $cfg): void
    {
        $missing = [];
        if ($cfg['host'] === '') {
            $missing[] = 'DB_HOST';
        }
        if ($cfg['port'] === '') {
            $missing[] = 'DB_PORT';
        }
        if ($cfg['name'] === '') {
            $missing[] = 'DB_DATABASE';
        }
        if ($cfg['user'] === '') {
            $missing[] = 'DB_USERNAME';
        }
        if (!Env::has('DB_PASSWORD') && !Env::has('DB_PASS')) {
            $missing[] = 'DB_PASSWORD';
        }

        if ($missing !== []) {
            throw new RuntimeException('Variável ausente no .env: ' . implode(', ', $missing));
        }
    }
}
