<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Dot-notation access over the configuration array plus the resolved
 * runtime paths. Paths are discovered dynamically so the same codebase works
 * whether the application lives beside public_html/ or inside it.
 */
final class Paths
{
    private static ?string $root = null;
    private static ?string $public = null;

    /**
     * Resolve the application layout.
     *
     * $entry is the file that boots the app (usually public_html/index.php).
     *
     * @param string $entry      Absolute path of the front controller
     * @param string $publicHint Absolute path of the public web root
     */
    public static function detect(string $entry, string $publicHint): void
    {
        $publicHint = rtrim(str_replace('\\', '/', trim($publicHint)), '/');
        self::$public = $publicHint === '' ? null : $publicHint;

        $entry = trim($entry);
        if ($entry !== '') {
            $entry = str_replace('\\', '/', $entry);
        }
        $entryDir = $entry === '' ? '' : rtrim(str_replace('\\', '/', dirname($entry)), '/');

        // Candidate roots, most specific first.
        $candidates = [
            $entryDir,                      // front controller IS the docroot
            $entryDir . '/..',              // standard: <root>/public_html/index.php
            $entryDir . '/../..',           // <root>/domains/<host>/public_html/index.php
            self::$public === null ? '' : dirname(self::$public),
        ];

        foreach ($candidates as $candidate) {
            $candidate = rtrim(str_replace('\\', '/', $candidate), '/');
            if ($candidate === '') {
                continue;
            }
            if (is_dir($candidate . '/app/Core') || is_file($candidate . '/app/bootstrap.php')) {
                self::$root = $candidate;
                break;
            }
        }

        if (self::$root === null) {
            // No candidate matched (for example `php -r` has no script file).
            // The directory holding this file's parent is the only sane guess.
            self::$root = rtrim(str_replace('\\', '/', dirname(__DIR__, 2)), '/');
        }
    }

    public static function root(): string
    {
        return self::$root ?? (self::$root = rtrim(str_replace('\\', '/', dirname(__DIR__, 2)), '/'));
    }

    public static function public(): string
    {
        return self::$public ?? (self::$public = self::root() . '/public_html');
    }

    public static function app(): string
    {
        return self::root() . '/app';
    }

    public static function config(): string
    {
        return self::root() . '/config';
    }

    public static function views(): string
    {
        return self::root() . '/views';
    }

    public static function database(): string
    {
        return self::root() . '/database';
    }

    public static function storage(): string
    {
        return self::root() . '/storage';
    }

    /** True when sensitive directories sit inside the web root (fallback mode). */
    public static function isSelfContained(): bool
    {
        return str_starts_with(self::root() . '/', self::public() . '/');
    }

    /** Ensure a writable directory exists (0700, parents included). */
    public static function ensureWritable(string $dir): bool
    {
        if (is_dir($dir)) {
            return is_writable($dir);
        }

        if (!@mkdir($dir, 0700, true) && !is_dir($dir)) {
            return false;
        }

        @chmod($dir, 0700);

        return is_writable($dir);
    }
}
