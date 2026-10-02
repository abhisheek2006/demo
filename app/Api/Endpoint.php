<?php
declare(strict_types=1);

namespace App\Api;

use App\Content\Clinic;
use App\Core\Exceptions\HttpException;
use App\Core\Logger;
use App\Core\Paths;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use Throwable;

/**
 * Shared plumbing for the JSON endpoints under public_html/api/.
 *
 * The public pages are static HTML, so nothing server-renders them any more.
 * Forms post here instead; every check (CSRF, rate limit, honeypot, spam
 * scoring, validation, persistence, notification mail) still happens in the
 * existing controllers — this class only translates their session-flash based
 * responses into JSON.
 */
final class Endpoint
{
    /**
     * Refuse direct requests for sensitive files and enforce the PHP version.
     * Mirrors the guards in public_html/index.php.
     */
    public static function guard(): void
    {
        $path = rawurldecode(strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/');

        if (preg_match('#(^|/)\.(?!well-known)#', $path)
            || preg_match('#\.(sql|sql\.gz|bak|log|ini|sh|jsonl|md5|sha1|lock|dist|example)$#i', $path)
            || preg_match('#/(app|config|views|database|storage|logs|sessions|backups|tools)/#i', $path)
        ) {
            self::abort(404, 'Not found.');
        }

        if (PHP_VERSION_ID < 80100) {
            self::abort(500, 'This server runs an unsupported PHP version.');
        }
    }

    /** Return the live request, booting the application if nobody has yet. */
    public static function request(): Request
    {
        static $request = null;

        if ($request === null) {
            // The endpoint scripts under public_html/api/ already load the
            // bootstrap, so only fall back to booting here.
            if (!class_exists(Request::class, false)) {
                require dirname(__DIR__, 2) . '/app/bootstrap.php';
            }

            $request = Request::capture(Paths::public());
        }

        return $request;
    }

    /**
     * Only POST is accepted on the form endpoints.
     */
    public static function requirePost(Request $request): void
    {
        if (!$request->isPost()) {
            self::respond(['ok' => false, 'message' => 'This endpoint accepts POST requests only.'], 405);
        }
    }

    /**
     * Run a form handler and translate whatever it produced into JSON.
     *
     * @param callable(Request):Response $handler
     */
    public static function form(Request $request, callable $handler): void
    {
        // A static page cannot render server-side errors, so browser posts
        // without JavaScript are bounced back to the form page with a status
        // flag that main.js turns into a banner.
        $asJson = $request->wantsJson();

        try {
            $response = $handler($request);
            $status   = $response->status();
        } catch (HttpException $e) {
            $status = $e->statusCode();

            if (!$asJson) {
                self::redirectBack($request, $status);
            }

            // The controller flashes the per-field errors and then aborts with
            // a 302, so they have to be read here: the code below that normally
            // reports them never runs for a thrown validation failure.
            $errors = Session::flashErrors();
            $old    = Session::flashOld();
            $reason = $e->getMessage();

            self::respond([
                'ok'      => false,
                'message' => $errors !== []
                    ? 'Please correct the highlighted fields and try again.'
                    : ($reason !== '' && $reason !== 'Redirecting'
                        ? $reason
                        : self::statusMessage($status)),
                'errors'  => $errors !== [] ? $errors : (object) [],
                'old'     => $old,
            ], self::jsonStatus($status));
        } catch (Throwable $e) {
            Logger::error('api.error', 'Form endpoint failed', [
                'exception' => $e::class,
                'message'   => $e->getMessage(),
            ]);

            if (!$asJson) {
                self::redirectBack($request, 500);
            }
            self::respond([
                'ok'      => false,
                'message' => 'Something went wrong on our side. Please call the clinic on '
                    . (Clinic::info()['phone'] ?? 'the number on the page')
                    . ' and we will help you directly.',
                'errors'  => (object) [],
            ], 500);
        }

        // Validation failures are flashed by the controller before it throws a
        // 302 back to the form; success notices are flashed before the redirect.
        $errors    = Session::flashErrors();
        $old       = Session::flashOld();
        $success   = Session::getFlash('success');
        $errorText = Session::getFlash('error');

        // Some controllers answer JSON themselves and skip the flash entirely.
        // Their payload is already the right shape, so pass it straight on
        // instead of inventing a failure from the redirect status.
        if ($asJson && $response->header('Content-Type') !== ''
            && str_contains(strtolower($response->header('Content-Type')), 'json')
        ) {
            self::respondWithResponse($response);
        }

        if ($errors !== []) {
            if (!$asJson) {
                self::redirectBack($request, 422);
            }
            self::respond([
                'ok'      => false,
                'message' => is_string($errorText) && $errorText !== ''
                    ? $errorText
                    : 'Please correct the highlighted fields and try again.',
                'errors'  => $errors,
                'old'     => $old,
            ], 422);
        }

        if (is_string($success) && $success !== '') {
            if (!$asJson) {
                self::redirectBack($request, 200);
            }
            self::respond(['ok' => true, 'message' => $success], 200);
        }

        if ($status >= 500) {
            if (!$asJson) {
                self::redirectBack($request, 500);
            }
            self::respond([
                'ok'      => false,
                'message' => 'Something went wrong on our side. Please try again or call the clinic.',
                'errors'  => (object) [],
            ], 500);
        }

        if (!$asJson) {
            self::redirectBack($request, self::jsonStatus($status));
        }

        self::respond([
            'ok'      => false,
            'message' => is_string($errorText) && $errorText !== ''
                ? $errorText
                : self::statusMessage($status),
            'errors'  => (object) [],
        ], self::jsonStatus($status));
    }

    /**
     * Send a no-JavaScript visitor back to the page the form lives on.
     *
     * Only same-site referrers are honoured, and the flag is read by main.js to
     * show a success or retry banner.
     */
    private static function redirectBack(Request $request, int $status): never
    {
        $referer = (string) ($request->header('Referer') ?? '');
        $target  = '';

        if ($referer !== '') {
            $parts = parse_url($referer);
            $path  = (string) ($parts['path'] ?? '');

            // Compare hostnames only: ports differ between local testing and
            // production but the host must match exactly, or we drop the referer.
            $refererHost = strtolower(explode(':', (string) ($parts['host'] ?? ''))[0]);
            $ownHost     = strtolower(explode(':', (string) $request->server('HTTP_HOST', ''))[0]);

            if ($refererHost !== '' && $refererHost === $ownHost && $path !== '') {
                $target = $path;
            }
        }

        if ($target === '') {
            $target = '/contact';
        }

        $flag = match (true) {
            $status === 200 => 'sent=1',
            $status === 429 => 'limited=1',
            $status >= 500  => 'error=1',
            default         => 'invalid=1',
        };

        header('Location: ' . $target . (str_contains($target, '?') ? '&' : '?') . $flag, true, 302);
        exit;
    }

    /** A plain JSON body, always with no-store so tokens never get cached. */
    public static function respond(array $payload, int $status = 200): never
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Robots-Tag: noindex, nofollow');
        header('Vary: Accept, X-Requested-With');
        header('X-Content-Type-Options: nosniff');
        echo $json === false ? '{"ok":false,"message":"Encoding error"}' : $json;

        exit;
    }

    /** Relay a JSON response the controller produced itself. */
    private static function respondWithResponse(Response $response): never
    {
        http_response_code($response->status());
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Robots-Tag: noindex, nofollow');
        header('Vary: Accept, X-Requested-With');
        header('X-Content-Type-Options: nosniff');
        echo $response->body();

        exit;
    }

    /** Turn redirect statuses into meaningful HTTP codes. */
    private static function jsonStatus(int $status): int
    {
        return match ($status) {
            301, 302, 303, 307, 308 => 422, // validation bounce back to the form
            401, 403                  => 403,
            419                       => 419,
            default                   => $status >= 400 ? $status : 422,
        };
    }

    private static function statusMessage(int $status): string
    {
        return match ($status) {
            403      => 'This request was blocked. Please reload the page and try again.',
            419      => 'Your session expired for security reasons. Please reload the page and submit again.',
            404      => 'Endpoint not found.',
            405      => 'This endpoint accepts POST requests only.',
            429      => 'Too many submissions from this device. Please wait a few minutes, or call the clinic.',
            default  => 'Please check the form and try again.',
        };
    }

    private static function abort(int $status, string $message): never
    {
        self::respond(['ok' => false, 'message' => $message], $status);
    }
}