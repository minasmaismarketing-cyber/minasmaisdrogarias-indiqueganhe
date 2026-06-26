<?php

declare(strict_types=1);

class RateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_MINUTES = 15;

    public static function tooManyAttempts(string $key): bool
    {
        $record = self::find($key);

        if ($record === null) {
            return false;
        }

        if ($record['bloqueado_ate'] !== null && strtotime($record['bloqueado_ate']) > time()) {
            return true;
        }

        return (int) $record['tentativas'] >= self::MAX_ATTEMPTS;
    }

    public static function hit(string $key): void
    {
        $db = Database::getConnection();
        $record = self::find($key);

        if ($record === null) {
            $stmt = $db->prepare(
                'INSERT INTO login_tentativas (chave, tentativas) VALUES (:chave, 1)'
            );
            $stmt->execute(['chave' => $key]);
            return;
        }

        $attempts = (int) $record['tentativas'] + 1;
        $blockedUntil = null;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $blockedUntil = date('Y-m-d H:i:s', time() + (self::DECAY_MINUTES * 60));
            $attempts = self::MAX_ATTEMPTS;
        }

        $stmt = $db->prepare(
            'UPDATE login_tentativas
             SET tentativas = :tentativas, bloqueado_ate = :bloqueado_ate
             WHERE chave = :chave'
        );
        $stmt->execute([
            'tentativas' => $attempts,
            'bloqueado_ate' => $blockedUntil,
            'chave' => $key,
        ]);
    }

    public static function clear(string $key): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM login_tentativas WHERE chave = :chave');
        $stmt->execute(['chave' => $key]);
    }

    public static function remainingSeconds(string $key): int
    {
        $record = self::find($key);

        if ($record === null || $record['bloqueado_ate'] === null) {
            return 0;
        }

        return max(0, strtotime($record['bloqueado_ate']) - time());
    }

    public function attempt(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $record = self::find($key);

        if ($record !== null) {
            if ($record['bloqueado_ate'] !== null && strtotime($record['bloqueado_ate']) > time()) {
                return false;
            }

            if ($record['bloqueado_ate'] !== null && strtotime($record['bloqueado_ate']) <= time()) {
                self::clear($key);
                $record = null;
            } elseif ((int) $record['tentativas'] >= $maxAttempts) {
                return false;
            }
        }

        $db = Database::getConnection();

        if ($record === null) {
            $stmt = $db->prepare(
                'INSERT INTO login_tentativas (chave, tentativas) VALUES (:chave, 1)'
            );
            $stmt->execute(['chave' => $key]);

            return true;
        }

        $attempts = (int) $record['tentativas'] + 1;
        $blockedUntil = null;

        if ($attempts >= $maxAttempts) {
            $blockedUntil = date('Y-m-d H:i:s', time() + $windowSeconds);
        }

        $stmt = $db->prepare(
            'UPDATE login_tentativas
             SET tentativas = :tentativas, bloqueado_ate = :bloqueado_ate
             WHERE chave = :chave'
        );
        $stmt->execute([
            'tentativas' => $attempts,
            'bloqueado_ate' => $blockedUntil,
            'chave' => $key,
        ]);

        return true;
    }

    /** @return array<string, mixed>|null */
    private static function find(string $key): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM login_tentativas WHERE chave = :chave LIMIT 1');
            $stmt->execute(['chave' => $key]);
            $row = $stmt->fetch();

            return $row ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
