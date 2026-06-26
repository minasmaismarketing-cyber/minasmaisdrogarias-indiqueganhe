<?php

declare(strict_types=1);

class Logger
{
    private static string $logFile = '';

    private static function logFile(): string
    {
        if (self::$logFile === '') {
            self::$logFile = BASE_PATH . '/uploads/logs/app.log';
        }

        return self::$logFile;
    }

    public static function write(string $level, string $message, array $context = []): void
    {
        $dir = dirname(self::logFile());

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextJson = $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$timestamp}] [{$level}] {$message}{$contextJson}" . PHP_EOL;

        file_put_contents(self::logFile(), $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }
}
