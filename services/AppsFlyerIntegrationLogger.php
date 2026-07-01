<?php

declare(strict_types=1);

/**
 * Logs dedicados da integração AppsFlyer — canais separados (Sprint 3.2).
 *
 * Arquivos em uploads/logs/appsflyer/:
 * - onelink.log
 * - webhook.log
 * - api_kobe.log
 * - errors.log
 */
final class AppsFlyerIntegrationLogger
{
    private const LOG_DIR = '/uploads/logs/appsflyer';

    /** @param array<string, mixed> $usuario */
    public static function logOneLinkGenerated(
        array $usuario,
        string $codigo,
        string $url,
        string $linkType,
        float $durationMs,
        array $extra = []
    ): void {
        if (!AppsFlyerConfig::isHomologation()) {
            return;
        }

        self::write('onelink', [
            'event' => 'onelink_generated',
            'usuario_id' => $usuario['id'] ?? null,
            'usuario_nome' => $usuario['nome'] ?? null,
            'codigo_indicador' => $codigo,
            'url' => $url,
            'link_type' => $linkType,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => self::timestamp(),
            'extra' => $extra,
        ]);
    }

    /** @param array<string, mixed> $payload */
    public static function logWebhookReceived(
        array $payload,
        array $headers,
        string $ip,
        float $durationMs,
        int $statusCode,
        array $response = []
    ): void {
        if (!AppsFlyerConfig::isHomologation()) {
            return;
        }

        self::write('webhook', [
            'event' => 'webhook_received',
            'ip' => $ip,
            'headers' => $headers,
            'payload' => $payload,
            'response' => $response,
            'status_code' => $statusCode,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => self::timestamp(),
        ]);
    }

    /** @param array<string, mixed> $received
     *  @param array<string, mixed> $sent
     */
    public static function logApiKobe(
        array $received,
        array $sent,
        int $statusCode,
        string $ip,
        float $durationMs
    ): void {
        if (!AppsFlyerConfig::isHomologation()) {
            return;
        }

        self::write('api_kobe', [
            'event' => 'api_kobe_call',
            'ip' => $ip,
            'payload_received' => $received,
            'payload_sent' => $sent,
            'status_code' => $statusCode,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => self::timestamp(),
        ]);
    }

    /** @param array<string, mixed> $context */
    public static function logError(string $channel, string $message, array $context = []): void
    {
        self::write('errors', [
            'event' => 'integration_error',
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
            'timestamp' => self::timestamp(),
        ]);
    }

    public static function readLastEntry(string $channel): ?array
    {
        $file = self::logPath($channel);

        if (!is_file($file)) {
            return null;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false || $lines === []) {
            return null;
        }

        $lastLine = (string) end($lines);
        $decoded = json_decode($lastLine, true);

        return is_array($decoded) ? $decoded : ['raw' => $lastLine];
    }

    /** @param array<string, mixed> $entry */
    private static function write(string $channel, array $entry): void
    {
        $dir = BASE_PATH . self::LOG_DIR;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents(self::logPath($channel), $line, FILE_APPEND | LOCK_EX);
    }

    private static function logPath(string $channel): string
    {
        return BASE_PATH . self::LOG_DIR . '/' . $channel . '.log';
    }

    private static function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /** @return array<string, string> */
    public static function captureRequestHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (!is_string($value) || !str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $name = str_replace('_', '-', strtolower(substr($key, 5)));
            $headers[$name] = $value;
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }
}
