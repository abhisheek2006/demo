<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP response with automatic security headers, CSP hash generation for
 * JSON-LD blocks and pretty error pages.
 */
final class Response
{
    private int $status = 200;

    /** @var array<string,string> */
    private array $headers = [];

    /** @var string[] */
    private array $cookies = [];

    private string $body = '';

    private bool $headersSentFlag = false;

    public function __construct(string $body = '', int $status = 200)
    {
        $this->body   = $body;
        $this->status = $status;
    }

    public static function make(string $body = '', int $status = 200): self
    {
        return new self($body, $status);
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[strtolower($name)] = $value;

        return $this;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function header(string $name, string $default = ''): string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function cookie(string $name, string $value, array $options = []): self
    {
        $this->cookies[] = ['name' => $name, 'value' => $value, 'options' => $options];

        return $this;
    }

    public function redirect(string $location, int $status = 302): self
    {
        $this->setStatus($status);
        $this->setHeader('Location', $location);

        return $this;
    }

    public function json(array $data, int $status = 200): self
    {
        $this->setStatus($status);
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $this->body = (string) json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $this;
    }

    public function noCache(): self
    {
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Expires', '0');

        return $this;
    }

    public function cache(int $seconds, bool $public = true): self
    {
        $this->setHeader('Cache-Control', ($public ? 'public' : 'private') . ', max-age=' . $seconds);

        return $this;
    }

    public function isRedirect(): bool
    {
        return $this->status >= 300 && $this->status < 400 && $this->hasHeader('Location');
    }

    /* ------------------------------------------------------------------ */
    /* Security headers                                                     */
    /* ------------------------------------------------------------------ */

    public function withSecurityHeaders(bool $isHttps, bool $isAdmin = false): self
    {
        $this->setHeader('X-Content-Type-Options', 'nosniff');
        $this->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), interest-cohort=()');
        $this->setHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $this->setHeader('X-Permitted-Cross-Domain-Policies', 'none');

        if ($isAdmin) {
            $this->setHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        }

        if ($isHttps) {
            $maxAge = (int) Config::get('security.hsts_max_age', 15552000);
            $this->setHeader('Strict-Transport-Security', 'max-age=' . $maxAge . '; includeSubDomains; preload');
        }

        return $this;
    }

    public function withCsp(bool $isHttps): self
    {
        // JSON-LD blocks are inlined by the SEO service; hash them so the CSP
        // can stay strict without 'unsafe-inline'.
        $scriptSrc = "'self'";
        foreach (self::inlineScriptHashes($this->body) as $hash) {
            $scriptSrc .= " 'sha256-{$hash}'";
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self' https://wa.me",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "img-src 'self' data:",
            "font-src 'self'",
            "style-src 'self'",
            'script-src ' . $scriptSrc,
            "connect-src 'self'",
            "manifest-src 'self'",
        ];

        if ($isHttps) {
            $directives[] = 'upgrade-insecure-requests';
        }

        $this->setHeader('Content-Security-Policy', implode('; ', $directives));

        return $this;
    }

    /** @return string[] base64 sha256 hashes of inline script blocks */
    public static function inlineScriptHashes(string $html): array
    {
        if (!str_contains($html, '<script')) {
            return [];
        }

        $hashes = [];
        if (preg_match_all('#<script\b(?![^>]*\bsrc=)[^>]*>(.*?)</script>#is', $html, $matches)) {
            foreach ($matches[1] as $inner) {
                $hashes[] = base64_encode(hash('sha256', $inner, true));
            }
        }

        return array_values(array_unique($hashes));
    }

    /* ------------------------------------------------------------------ */

    public function send(bool $isHttps = false): void
    {
        if (!headers_sent()) {
            $this->applyHeaders($isHttps);
        }

        $this->headersSentFlag = true;

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'HEAD') {
            return;
        }

        echo $this->body;
    }

    private function applyHeaders(bool $isHttps): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            $canonical = implode('-', array_map('ucfirst', explode('-', $name)));
            header($canonical . ': ' . $value, true);
        }

        if ($this->body !== '' && !$this->hasHeader('Content-Type')) {
            header('Content-Type: text/html; charset=UTF-8', true);
        }

        foreach ($this->cookies as $cookie) {
            setcookie($cookie['name'], $cookie['value'], $cookie['options']);
        }
    }

    public function hasSent(): bool
    {
        return $this->headersSentFlag;
    }
}
