<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Response;
use App\Services\Seo;

/**
 * sitemap.xml, robots.txt and favicon-adjacent static endpoints.
 */
final class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $entries = Seo::sitemapEntries();


        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($entry['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . e($entry['lastmod']) . "</lastmod>\n";
            $xml .= '    <changefreq>' . e($entry['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . e($entry['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return (new Response($xml))
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setHeader('X-Robots-Tag', 'noindex, follow')
            ->cache(3600);
    }

    public function robots(): Response
    {
        $base = rtrim(Config::string('app.url', 'https://swastihomoeo.com'), '/');

        $lines = [
            '# robots.txt for ' . Config::string('app.name', 'Swasti Homoeo Clinic'),
            '# Generated ' . date('Y-m-d'),
            '',
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /storage/',
            'Disallow: /database/',
            'Disallow: /config/',
            'Disallow: /app/',
            'Disallow: /views/',
            'Disallow: /?submitted=1',
            'Disallow: /*?utm_',
            '',
            '# Crawl-delay keeps crawlers polite on shared hosting',
            'Crawl-delay: 1',
            '',
            'Sitemap: ' . $base . '/sitemap.xml',
            '',
        ];

        return (new Response(implode("\n", $lines)))
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->cache(86400);
    }
}
