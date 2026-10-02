<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Plain-PHP template renderer with layouts, sections and partials.
 * There is no template compilation step and no external dependency.
 */
final class View
{
    private static string $basePath = '';

    /** @var array<string,string> */
    private static array $sections = [];

    /** @var array<string,array<int,string>> */
    private static array $sectionStack = [];

    /** @var array<string,mixed> */
    private static array $shared = [];

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim(str_replace('\\', '/', $path), '/');
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /** @return array<string,mixed> */
    public static function shared(): array
    {
        return self::$shared;
    }

    public static function exists(string $template): bool
    {
        return is_file(self::resolve($template));
    }

    public static function resolve(string $template): string
    {
        $template = trim(str_replace('\\', '/', $template), '/');
        if ($template === '') {
            $template = 'pages/home';
        }
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        return self::$basePath . '/' . $template;
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        self::$sections = [];
        self::$sectionStack = [];

        $content = self::renderFile(self::resolve($template), $data);

        if ($layout === null || $layout === '') {
            return $content;
        }

        $layoutData = array_merge($data, [
            'content'      => $content,
            'sections'     => self::$sections,
            'sectionNames' => array_keys(self::$sections),
        ]);

        $layoutFile = self::resolve($layout);
        if (!is_file($layoutFile)) {
            throw new RuntimeException('Layout not found: ' . $layout);
        }

        return self::renderFile($layoutFile, $layoutData);
    }

    /** @param array<string,mixed> $data */
    public static function renderFile(string $file, array $data = []): string
    {
        if (!is_file($file)) {
            throw new RuntimeException('View not found: ' . $file);
        }

        $scope = array_merge(self::$shared, $data);
        $level = ob_get_level();

        ob_start();

        try {
            (static function (string $__file, array $__scope): void {
                extract($__scope, EXTR_SKIP);
                require $__file;
            })($file, $scope);
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return (string) ob_get_clean();
    }

    /** Render a component partial from views/components. */
    public static function partial(string $name, array $data = []): string
    {
        $name = str_replace(['components.', '.php'], '', trim($name, '/'));
        $file = self::resolve('components/' . $name);
        if (!is_file($file)) {
            return '';
        }

        return self::renderFile($file, $data);
    }

    /* ------------------------------ sections ----------------------------- */

    public static function start(string $name): void
    {
        self::$sectionStack[$name][] = '';
    }

    public static function stop(): void
    {
        $name = array_key_last(self::$sectionStack);
        if ($name === null) {
            return;
        }

        $html = (string) ob_get_clean();
        unset(self::$sectionStack[$name]);

        self::$sections[$name] = (self::$sections[$name] ?? '') . $html;
    }

    public static function section(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    public static function hasSection(string $name): bool
    {
        return isset(self::$sections[$name]) && trim(self::$sections[$name]) !== '';
    }
}
