<?php
declare(strict_types=1);

/**
 * Static page builder.
 *
 *     php tools/build-pages.php
 *
 * Renders every public page through the existing controllers and templates and
 * writes the result to standalone .html files in public_html/. The site is then
 * plain HTML on the front end; PHP only serves the form endpoints under
 * public_html/api/ and the admin panel.
 *
 * What it does:
 *   - renders each route to a full HTML document (same markup as before)
 *   - rewrites absolute APP_URL links to root-relative paths, so the pages work
 *     on any host and any domain
 *   - blanks the CSRF token (the browser fetches one from /api/csrf.php)
 *   - points forms at the JSON endpoints
 *   - writes robots.txt and sitemap.xml from the same SEO data as the templates
 *
 * Re-run it after editing anything in app/Content or views/.
 */

use App\Core\Config;
use App\Core\Paths;
use App\Core\Request;
use App\Core\Response;
use App\Services\Seo;

// Sessions are pointless here; keep the CLI out of the real session directory.
ini_set('session.save_path', sys_get_temp_dir() . '/swasti-build-' . getmypid());
@mkdir(ini_get('session.save_path'), 0700, true);

/** @var App\Core\Kernel $kernel */
$kernel = require dirname(__DIR__) . '/app/bootstrap.php';

$publicDir = rtrim(str_replace('\\', '/', dirname(__DIR__) . '/public_html'), '/');
$siteUrl   = rtrim(Config::string('app.url', ''), '/');

// bootstrap.php derives the web root from SCRIPT_FILENAME, which here points at
// tools/ rather than the front controller. Re-point it so helpers that touch the
// filesystem (asset(), has_image(), image_url()) resolve against public_html.
Paths::detect($publicDir . '/index.php', $publicDir);

/* ------------------------------------------------------------------------- */
/* Route list                                                                */
/* ------------------------------------------------------------------------- */

$pages = [
    ['route' => '/',                  'file' => 'index.html'],
    ['route' => '/about-us',          'file' => 'about-us.html'],
    ['route' => '/faq',               'file' => 'faq.html'],
    ['route' => '/book-consultation', 'file' => 'book-consultation.html'],
    ['route' => '/book-follow-up',    'file' => 'book-follow-up.html'],
    ['route' => '/contact',           'file' => 'contact.html'],
    ['route' => '/privacy-policy',    'file' => 'privacy-policy.html'],
];


/* ------------------------------------------------------------------------- */
/* Helpers                                                                   */
/* ------------------------------------------------------------------------- */

/** Render one route exactly as a visitor would receive it. */
function renderPage(App\Core\Kernel $kernel, string $route, string $publicDir): string
{
    // Swap in a fake server environment so Request::capture() does its normal
    // path normalisation (base-path stripping, trailing slashes) for us.
    $realServer = $_SERVER;
    $realGet    = $_GET;
    $realPost   = $_POST;

    $_GET    = [];
    $_POST   = [];
    $_SERVER = [
        'REQUEST_METHOD'  => 'GET',
        'REQUEST_URI'     => $route,
        'HTTP_HOST'       => 'localhost',
        'SERVER_NAME'     => 'localhost',
        'SCRIPT_NAME'     => '/index.php',
        'DOCUMENT_ROOT'   => $publicDir,
        'REMOTE_ADDR'     => '127.0.0.1',
        'HTTP_USER_AGENT' => 'StaticPageBuilder/1.0',
        'SERVER_PORT'     => '80',
    ];

    try {
        $request  = Request::capture($publicDir);
        /** @var Response $response */
        $response = $kernel->handle($request);
    } finally {
        $_SERVER = $realServer;
        $_GET    = $realGet;
        $_POST   = $realPost;
    }

    $html = $response->body();

    if ($html === '' || strpos($html, '<!DOCTYPE html>') === false) {
        throw new RuntimeException('Route ' . $route . ' did not render an HTML document.');
    }

    return $html;
}

/**
 * Convert rendered output into a deployable static page.
 */
function staticise(string $html, string $siteUrl): string
{
    // 1. Internal links and assets become root-relative so the bundle is
    //    portable across domains. Absolute URLs are only kept where a crawler
    //    genuinely needs them (canonical, og:url, JSON-LD) and those are handled
    //    below by leaving them alone.
    if ($siteUrl !== '') {
        // Only genuinely absolute tags survive: canonical, the Open Graph URLs
        // social crawlers read, and the JSON-LD graph. Stylesheets, icons, the
        // web manifest and every internal link are rewritten to root-relative
        // so the same files work on any domain.
        $protected = [];
        $html = preg_replace_callback(
            '#<(link|meta)\b[^>]*(?:rel="canonical"|property="og:(?:url|image)"|'
            . 'name="twitter:image")[^>]*>#i',
            static function (array $m) use (&$protected): string {
                $protected[] = $m[0];

                return "\x00" . (count($protected) - 1) . "\x00";
            },
            $html
        );

        // Also protect inline JSON-LD blocks.
        $html = preg_replace_callback(
            '#<script type="application/ld\+json">.*?</script>#is',
            static function (array $m) use (&$protected): string {
                $protected[] = $m[0];

                return "\x00" . (count($protected) - 1) . "\x00";
            },
            $html
        );

        // Everything else: strip the scheme + host prefix.
        $html = str_replace([$siteUrl, $siteUrl . '/'], '', $html);
        $html = preg_replace_callback(
            '#\x00(\d+)\x00#',
            static fn (array $m): string => $protected[(int) $m[1]],
            $html
        );
    }

    // 2. No baked CSRF token in a static file: the browser fetches one.
    $html = preg_replace(
        '#(<input type="hidden" name="_token" value=")[^"]*(")#i',
        '$1$2',
        $html
    );

    // 3. Belt and braces — make sure no form still posts to a page URL.
    $html = str_replace('action="https://', 'action="', $html);

    return $html;
}

/** Robots.txt as a real file so Apache serves it without PHP. */
function buildRobots(string $siteUrl, string $env): string
{
    $lines = [];
    if ($env !== 'production') {
        $lines[] = '# Non-production environment (' . $env . ') — kept out of search results.';
        $lines[] = 'User-agent: *';
        $lines[] = 'Disallow: /';
        $lines[] = '';

        return implode("\n", $lines);
    }

    $lines[] = 'User-agent: *';
    $lines[] = 'Allow: /';
    $lines[] = 'Disallow: /admin/';
    $lines[] = 'Disallow: /api/';
    $lines[] = 'Disallow: /index.php';
    $lines[] = '';
    $lines[] = '# Ad crawlers: no ads on this site.';
    $lines[] = 'User-agent: Mediapartners-Google';
    $lines[] = 'Disallow: /';
    $lines[] = '';
    $lines[] = 'User-agent: AdsBot-Google';
    $lines[] = 'Disallow: /';
    $lines[] = '';
    if ($siteUrl !== '') {
        $lines[] = 'Sitemap: ' . $siteUrl . '/sitemap.xml';
    }
    $lines[] = '';

    return implode("\n", $lines);
}

/** sitemap.xml built from the same entries the PHP route used. */
function buildSitemap(): string
{
    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach (Seo::sitemapEntries() as $entry) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>' . htmlspecialchars((string) $entry['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= '    <lastmod>' . htmlspecialchars((string) $entry['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
        $xml .= '    <changefreq>' . htmlspecialchars((string) $entry['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
        $xml .= '    <priority>' . htmlspecialchars((string) $entry['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>' . "\n";

    return $xml;
}

/* ------------------------------------------------------------------------- */
/* Run                                                                       */
/* ------------------------------------------------------------------------- */

$written = 0;
$failed  = [];

foreach ($pages as $page) {
    $target = $publicDir . '/' . $page['file'];

    try {
        $html = staticise(renderPage($kernel, $page['route'], $publicDir), $siteUrl);

        $dir = dirname($target);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create ' . $dir);
        }

        file_put_contents($target, $html);
        printf("  %-52s %7.1f KB\n", $page['file'], strlen($html) / 1024);
        $written++;
    } catch (Throwable $e) {
        $failed[] = $page['route'] . ': ' . $e->getMessage();
        fwrite(STDERR, '  FAILED ' . $page['route'] . ' — ' . $e->getMessage() . "\n");
    }
}

// 404 page: render an unknown route so the branded error page is captured.
try {
    $html = staticise(renderPage($kernel, '/__build-missing-page__', $publicDir), $siteUrl);
    // A static 404 must never be indexed and must not advertise the app URL.
    $html = preg_replace('/<meta name="robots" content="[^"]*">/', '<meta name="robots" content="noindex, follow">', $html);
    // The throwaway route used to render it is not a real address, so drop the
    // canonical and og:url it inherited rather than shipping them.
    $html = preg_replace('#<link rel="canonical"[^>]*>#i', '', (string) $html);
    $html = preg_replace('#<meta property="og:url"[^>]*>#i', '', (string) $html);
    // The JSON-LD graph also describes the throwaway route; a 404 page should
    // not ship structured data at all.
    $html = preg_replace('#<script type="application/ld\+json">.*?</script>#is', '', (string) $html);
    // The same throwaway path is printed in the "requested path" note. The one
    // static 404 is served for every missing URL, so main.js fills in whatever
    // the visitor actually asked for.
    $html = preg_replace_callback(
        '#(<p class="error-path">)(.*?)(</p>)#is',
        static fn (array $m): string => $m[1]
            . (preg_replace(
                '#<code>.*?</code>#is',
                '<code data-404-path>the page you requested</code>',
                $m[2]
            ) ?? $m[2])
            . $m[3],
        (string) $html
    );
    file_put_contents($publicDir . '/404.html', $html);
    printf("  %-52s %7.1f KB\n", '404.html', strlen($html) / 1024);
    $written++;
} catch (Throwable $e) {
    $failed[] = '404: ' . $e->getMessage();
    fwrite(STDERR, "  FAILED 404 — " . $e->getMessage() . "\n");
}

// SEO files.
$env = Config::string('app.env', 'production');
file_put_contents($publicDir . '/robots.txt', buildRobots($siteUrl, $env));
file_put_contents($publicDir . '/sitemap.xml', buildSitemap());
printf("  %-52s %7.1f KB\n", 'robots.txt', strlen(buildRobots($siteUrl, $env)) / 1024);
printf("  %-52s %7.1f KB\n", 'sitemap.xml', strlen(buildSitemap()) / 1024);
$written += 2;

// Clean up the throwaway session directory.
foreach ((array) glob(ini_get('session.save_path') . '/sess_*') as $file) {
    @unlink((string) $file);
}
@rmdir(ini_get('session.save_path'));

echo "\n";
if ($failed !== []) {
    fwrite(STDERR, sprintf("%d file(s) failed:\n  - %s\n", count($failed), implode("\n  - ", $failed)));
    exit(1);
}

printf(
    "%d file(s) written to public_html/ (site URL: %s, env: %s).%s",
    $written,
    $siteUrl !== '' ? $siteUrl : 'not set — set APP_URL in .env for correct canonical tags',
    $env,
    PHP_EOL
);