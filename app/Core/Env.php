<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal, dependency-free .env reader.
 *
 * Supports KEY=value, quoted values, # comments, blank lines and an optional
 * leading "export ". Values already present in the real environment always win,
 * so Hostinger's own environment variables override the file when set.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $values = [];

    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            return;
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || $line[0] === ';' || $line[0] === '[') {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $raw = trim(substr($line, $eq + 1));

            if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $key)) {
                continue;
            }

            $value = self::unquote($raw);

            // Strip trailing inline comments only for unquoted values.
            if ($value['quoted'] === false) {
                $value['value'] = self::stripComment($value['value']);
            }

            // Precedence: real environment > .env file.
            $existing = getenv($key);
            if ($existing === false && !isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                self::$values[$key] = $value['value'];
            }
        }

        fclose($handle);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        if (array_key_exists($key, $_SERVER) && !is_array($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        return $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key);

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function isLocalEnv(): bool
    {
        return self::string('APP_ENV', 'production') === 'local';
    }

    /**
     * Build a secret from APP_KEY when available, otherwise a per-install
     * random value stored in storage. Never blocks application boot.
     */
    public static function appKey(string $storageDir = ''): string
    {
        $key = self::string('APP_KEY');

        if ($key !== '') {
            return $key;
        }

        if ($storageDir !== '' && is_dir($storageDir) && is_writable($storageDir)) {
            $file = rtrim($storageDir, '/\\') . DIRECTORY_SEPARATOR . '.app_key';
            if (is_file($file)) {
                $stored = @file_get_contents($file);
                if (is_string($stored) && trim($stored) !== '') {
                    return trim($stored);
                }
            }
            $generated = bin2hex(random_bytes(32));
            if (@file_put_contents($file, $generated, LOCK_EX) !== false) {
                @chmod($file, 0600);
            }

            return $generated;
        }

        return hash('sha256', 'swasti-homoeo-fallback-key');
    }

    /** @return array<string,string> */
    private static function unquote(string $raw): array
    {
        if ($raw === '') {
            return ['value' => '', 'quoted' => true];
        }

        $first = $raw[0];

        if (($first === '"' || $first === "'") && strlen($raw) > 1) {
            $last = substr($raw, -1);
            if ($last === $first) {
                $inner = substr($raw, 1, -1);

                return ['value' => $first === '"' ? self::unescapeDouble($inner) : $inner, 'quoted' => true];
            }
        }

        return ['value' => trim($raw), 'quoted' => false];
    }

    private static function stripComment(string $value): string
    {
        $pos = strpos($value, ' #');
        if ($pos !== false) {
            $value = substr($value, 0, $pos);
        }

        return trim($value, " \t");
    }

    private static function unescapeDouble(string $value): string
    {
        return strtr($value, [
            '\\n'  => "\n",
            '\\r'  => "\r",
            '\\t'  => "\t",
            '\\"'  => '"',
            '\\\\' => '\\',
        ]);
    }
}
