<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Runtime configuration with dot access, e.g. Config::get('forms.rate_limits').
 */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];

    public static function load(array $items): void
    {
        self::$items = $items;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return self::$items;
        }

        $node = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return $default;
            }
            $node = $node[$segment];
        }

        return $node;
    }

    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $node = &self::$items;
        foreach ($segments as $segment) {
            if (!isset($node[$segment]) || !is_array($node[$segment])) {
                $node[$segment] = [];
            }
            $node = &$node[$segment];
        }
        $node = $value;
    }

    public static function has(string $key): bool
    {
        $sentinel = new \stdClass();

        return self::get($key, $sentinel) !== $sentinel;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    public static function appEnv(): string
    {
        return strtolower(self::string('app.env', 'production'));
    }

    public static function debug(): bool
    {
        return self::bool('app.debug', false) && self::appEnv() !== 'production';
    }
}
