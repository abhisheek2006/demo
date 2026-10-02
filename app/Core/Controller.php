<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Services\Seo;

/**
 * Shared controller behaviour: view rendering, redirects, SEO metadata,
 * CSRF guards and admin session checks.
 */
abstract class Controller
{
    protected Request $request;

    protected string $layout = 'layouts/public';

    /** @var array<string,mixed> */
    protected array $seo = [];

    /** @var array<string,mixed> */
    protected array $viewData = [];

    /** Last validator instance, used when flashing errors back to a form. */
    protected ?Validator $lastValidator = null;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? Request::capture(Paths::public());
    }

    public function request(): Request
    {
        return $this->request;
    }

    /**
     * @param array<string,mixed> $data
     */
    protected function view(string $template, array $data = [], ?string $layout = null): Response
    {
        $viewData = array_merge($this->viewData, $data, ['seo' => $this->seoData()]);

        $html = View::render($template, $viewData, $layout ?? $this->layout);

        return (new Response($html, 200))
            ->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        $target = str_starts_with($path, 'http') ? $path : $this->request->url($path);

        return (new Response('', $status))->redirect($target, $status);
    }

    /** @param array<string,mixed> $payload */
    protected function json(array $payload, int $status = 200): Response
    {
        return (new Response())->json($payload, $status)->noCache();
    }

    /** @param array<string,mixed> $headers */
    protected function abort(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }

    /* ------------------------------- CSRF -------------------------------- */

    protected function assertCsrf(): void
    {
        $field = (string) Config::get('security.csrf_field', '_token');
        $token = $this->request->string($field);

        if (!Session::verifyCsrf($token)) {
            Logger::warning('csrf.reject', 'CSRF token mismatch', [
                'path' => $this->request->path(),
                'ip'   => $this->request->ip(),
            ]);

            if ($this->request->wantsJson()) {
                throw new HttpException(419, 'Your session expired. Please refresh the page and try again.');
            }

            Session::flashError('Your session expired for security reasons. Please review the form and submit again.');

            $referer = $this->request->header('Referer');
            $back    = $referer !== null && $this->isInternal($referer) ? $referer : '/';

            throw new HttpException(419, 'CSRF token mismatch', ['Location' => $back]);
        }
    }

    protected function isInternal(string $url): bool
    {
        $host = (string) $this->request->server('HTTP_HOST', '');

        return $host !== '' && str_contains($url, $host);
    }

    /* ------------------------------ validation --------------------------- */

    /**
     * @param array<string,string|array<int,string>> $rules
     * @param array<string,string> $labels
     */
    protected function validate(array $rules, array $labels = []): Validator
    {
        return $this->lastValidator = Validator::make($this->request->all(), $rules, $labels);
    }

    /**
     * Flash validation errors and abort the request with a redirect back to
     * the originating form. Declared `never` so callers cannot continue with
     * unvalidated data.
     */
    protected function validationFailed(Validator $validator, string $fallbackPath): never
    {
        $redirect = $fallbackPath;
        $referer  = $this->request->header('Referer');
        if ($referer !== null && $this->isInternal($referer)) {
            $redirect = $referer;
        }

        $target = str_starts_with($redirect, 'http') ? $redirect : $this->request->url($redirect);

        Session::flashValidation($validator->errors(), $this->safeOld($this->request->all()));

        throw new HttpException(302, 'Redirecting', ['Location' => $target]);
    }

    protected function redirectToForm(string $fallbackPath): void
    {
        $referer = $this->request->header('Referer');
        $path    = $referer !== null && $this->isInternal($referer)
            ? $referer
            : $this->request->url($fallbackPath);

        Session::set('_redirect_url', $path);
    }

    /* -------------------------------- SEO -------------------------------- */

    /** @param array<string,mixed> $meta */
    protected function setSeo(array $meta): void
    {
        $this->seo = array_merge($this->seo, $meta);
    }

    /** @return array<string,mixed> */
    protected function seoData(): array
    {
        return Seo::defaults($this->seo, $this->request);
    }

    /* -------------------------------- misc ------------------------------- */

    /** @param array<string,mixed> $data */
    protected function safeOld(array $data): array
    {
        unset($data['_token'], $data['password'], $data['website'], $data['form_started_at']);

        return $data;
    }

    protected function isAdminRoute(): bool
    {
        return str_starts_with($this->request->path(), '/admin');
    }
}
