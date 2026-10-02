<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Append-only file logger with size-based rotation. Never throws.
 */
final class Logger
{
    private static ?string $directory = null;

    public static function directory(): string
    {
        return self::$directory ??= Paths::storage() . '/logs';
    }

    public static function info(string $channel, string $message, array $context = []): void
    {
        self::write('info', $channel, $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::write('warning', $channel, $message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::write('error', $channel, $message, $context);
    }

    public static function write(string $level, string $channel, string $message, array $context = []): void
    {
        $dir = self::directory();
        if (!Paths::ensureWritable($dir)) {
            return;
        }

        try {
            $file = $dir . '/app-' . date('Y-m') . '.log';
            self::rotateIfNeeded($file, 2 * 1024 * 1024);

            $record = [
                'ts'      => date('c'),
                'level'   => $level,
                'channel' => $channel,
                'msg'     => mb_substr($message, 0, 2000),
            ];

            if ($context !== []) {
                $record['ctx'] = self::scrub($context);
            }

            $line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

            @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            @chmod($file, 0640);
        } catch (Throwable) {
            // Logging must never break the request.
        }
    }

    private static function scrub(array $context): array
    {
        $clean = [];
        foreach ($context as $key => $value) {
            $key = (string) $key;
            if (preg_match('/(password|secret|token|hash|authorization|smtp)/i', $key)) {
                $clean[$key] = '[redacted]';
                continue;
            }
            $clean[$key] = is_scalar($value) || $value === null
                ? $value
                : (is_array($value) ? array_slice($context, 0, 20) : gettype($value));
        }

        return $clean;
    }

    private static function rotateIfNeeded(string $file, int $maxBytes): void
    {
        if (!is_file($file) || (int) @filesize($file) < $maxBytes) {
            return;
        }

        @rename($file, $file . '.' . date('Ymd-His') . '.1');
    }
}
