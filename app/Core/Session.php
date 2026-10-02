<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session wrapper: hardened cookie flags, flash messages, CSRF tokens and
 * rate-limit friendly session data.
 */
final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || PHP_SAPI === 'cli' || headers_sent()) {
            self::$started = true;

            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;

            return;
        }

        $storage = Paths::storage() . '/sessions';
        if (Paths::ensureWritable($storage)) {
            session_save_path($storage);
        }

        $isHttps = Request::capture(Paths::public())->isHttps();
        $secure  = Config::get('security.cookie_secure');
        $secure  = $secure === null ? $isHttps : (bool) $secure;

        $params = [
            'lifetime' => 0,                 // session cookie
            'path'     => (string) Config::get('security.cookie_path', '/'),
            'domain'   => (string) Config::get('security.cookie_domain', ''),
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => (string) Config::get('security.cookie_samesite', 'Lax'),
        ];

        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params($params);
        } else {
            session_set_cookie_params(0, $params['path'], $params['domain'], $params['secure'], true);
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        if (PHP_VERSION_ID >= 80400) {
            // PHP 8.4 replaced the pair below with a single directive.
            ini_set('session.sid_length_per_character', '8');
        } else {
            ini_set('session.sid_length', '48');
            ini_set('session.sid_bits_per_character', '5');
        }
        ini_set('session.gc_maxlifetime', (string) Config::get('security.session_lifetime', 7200));
        ini_set('session.cookie_httponly', '1');

        @session_start();
        self::$started = true;

        // Stable per-session seed used to fingerprint rate limits.
        if (!isset($_SESSION['_sid_seed'])) {
            $_SESSION['_sid_seed'] = bin2hex(random_bytes(6));
        }

        self::enforceIdleTimeout();
        self::rotateOccasionally();
    }

    public static function active(): bool
    {
        return self::$started && session_status() === PHP_SESSION_ACTIVE;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);

        return $value;
    }

    public static function regenerate(): void
    {
        if (self::active()) {
            @session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (!self::active()) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name() ?: 'swasti_session', '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'] ?? '/',
                'domain'   => $params['domain'] ?? '',
                'secure'   => (bool) ($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        @session_destroy();
        self::$started = false;
    }

    /* --------------------------- flash messages -------------------------- */

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    public static function flashSuccess(string $message): void
    {
        self::flash('success', $message);
    }

    public static function flashError(string $message): void
    {
        self::flash('error', $message);
    }

    /** @return array<string,string> */
    public static function flashErrors(): array
    {
        $errors = $_SESSION['_flash_errors'] ?? [];
        unset($_SESSION['_flash_errors']);

        return is_array($errors) ? $errors : [];
    }

    /** @param array<string,string> $errors */
    public static function flashValidation(array $errors, array $old = []): void
    {
        self::flash('error', 'Please correct the highlighted fields and submit again.');
        // Written under the same keys flashErrors()/flashOld() read on the next
        // request. Using self::flash() here would bury them inside the flash bag
        // where the drain can never see them.
        $_SESSION['_flash_errors'] = $errors;
        $_SESSION['_flash_old']    = $old;
    }

    /** @return array<string,mixed> */
    public static function flashOld(): array
    {
        $old = $_SESSION['_flash_old'] ?? [];
        unset($_SESSION['_flash_old']);

        return is_array($old) ? $old : [];
    }

    /* ------------------------------- CSRF -------------------------------- */

    public static function csrfToken(): string
    {
        $token = self::get('_csrf');

        if (!is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(32));
            self::set('_csrf', $token);
        }

        return $token;
    }

    public static function csrfField(): string
    {
        $name = (string) Config::get('security.csrf_field', '_token');

        return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="'
            . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verifyCsrf(?string $token): bool
    {
        $expected = self::get('_csrf');

        if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public static function rotateCsrf(): void
    {
        self::set('_csrf', bin2hex(random_bytes(32)));
    }

    /* ---------------------------- internals ------------------------------ */

    private static function enforceIdleTimeout(): void
    {
        $lifetime = (int) Config::get('security.session_lifetime', 7200);
        if ($lifetime <= 0) {
            return;
        }

        $now  = time();
        $last = (int) ($_SESSION['_last_activity'] ?? 0);

        if ($last > 0 && ($now - $last) > $lifetime) {
            $_SESSION = [];
            self::regenerate();
        }

        $_SESSION['_last_activity'] = $now;
    }

    private static function rotateOccasionally(): void
    {
        $issued = (int) ($_SESSION['_issued'] ?? 0);

        if ($issued === 0) {
            $_SESSION['_issued'] = time();

            return;
        }

        if (time() - $issued > 1800) {
            self::regenerate();
            $_SESSION['_issued'] = time();
        }
    }
}
