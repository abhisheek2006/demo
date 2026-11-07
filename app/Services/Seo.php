<?php
declare(strict_types=1);

namespace App\Services;

use App\Content\Clinic;
use App\Content\Doctor;
use App\Core\Config;
use App\Core\Request;

/**
 * Central SEO metadata builder and Schema.org graph generator.
 */
final class Seo
{
    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    public static function defaults(array $overrides = [], ?Request $request = null): array
    {
        $clinic  = Clinic::info();
        $appName = Config::string('app.name', Clinic::NAME);

        $title       = (string) ($overrides['title'] ?? self::defaultTitle());
        $description = (string) ($overrides['description'] ?? self::defaultDescription());
        $canonical   = (string) ($overrides['canonical'] ?? ($request !== null ? $request->url($request->path()) : url('/')));
        $image       = (string) ($overrides['image'] ?? url(Config::string('seo.default_image', '/assets/images/og/og-default.png')));
        $type        = (string) ($overrides['type'] ?? 'website');

        if ($title !== $appName && !str_contains($title, $appName)) {
            $title = $title . ' · ' . $appName;
        }

        return [
            'title'            => $title,
            'description'      => truncate($description, 300, ''),
            'canonical'        => $canonical,
            'image'            => $image,
            'type'             => $type,
            'site_name'        => $appName,
            'locale'           => Config::string('app.locale', 'en_IN'),
            'robots'           => (string) ($overrides['robots'] ?? ($overrides['noindex'] ?? false ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1')),
            'published_time'   => (string) ($overrides['published_time'] ?? ''),
            'modified_time'    => (string) ($overrides['modified_time'] ?? ''),
            'twitter_site'     => Config::string('seo.twitter_site', ''),
            'twitter_creator'  => (string) ($overrides['twitter_creator'] ?? ''),
            'json_ld'          => (array) ($overrides['json_ld'] ?? []),
            'org'              => $clinic,
        ];
    }

    public static function defaultTitle(): string
    {
        return Clinic::HEADLINE;
    }

    public static function defaultDescription(): string
    {
        return 'Swasti Homoeo Clinic in ' . Clinic::addressOneLine() . ', ' . Clinic::info()['city']
            . ' offers consultations with Dr. Smriti Das (BHMS, MD—WBUHS). Call ' . Clinic::info()['phone'] . ' to book.';
    }

    /* -------------------------------------------------------------------- */
    /* Schema.org graph fragments                                            */
    /* -------------------------------------------------------------------- */

    /** @return array<string,mixed> */
    public static function medicalClinic(): array
    {
        $clinic = Clinic::info();

        return [
            '@type'                => 'MedicalClinic',
            '@id'                  => url('/#clinic'),
            'name'                 => $clinic['name'],
            'legalName'            => Clinic::LEGAL_NAME,
            'alternateName'        => $clinic['name'] . ' — ' . Clinic::LOCALITY,
            'description'          => truncate(Clinic::intro() . ' Located at ' . $clinic['address_full'] . ', ' . $clinic['city'] . '.', 500, ''),
            'slogan'               => Clinic::TAGLINE,
            'url'                  => url('/'),
            'telephone'            => $clinic['phone'],
            'email'                => $clinic['email'],
            'image'                => [
                url('/assets/images/hero/clinic-room.jpg'),
                url('/assets/images/hero/clinic-room-2.jpg'),
                url('/assets/images/hero/clinic-room-3.jpg'),
                url('/assets/images/hero/clinic-room-4.jpg'),
                url('/assets/images/hero/clinic-room-5.jpg'),
            ],
            'logo'                 => url('/assets/images/icons/logo.png'),
            'currenciesAccepted'   => 'INR',
            'address'              => self::postalAddress(),
            'hasMap'               => Clinic::mapUrl(),
            'openingHoursSpecification' => self::openingHours(),
            'medicalSpecialty'     => 'Homeopathic',
            'areaServed'           => [
                ['@type' => 'City', 'name' => $clinic['city']],
                ['@type' => 'AdministrativeArea', 'name' => $clinic['state']],
            ],
            'employee'             => ['@id' => url('/about-us#doctor')],
            'sameAs'               => self::sameAs(),
        ];
    }

    /** @return array<string,mixed> */
    public static function webSite(): array
    {
        return [
            '@type'           => 'WebSite',
            '@id'             => url('/#website'),
            'url'             => url('/'),
            'name'            => Clinic::NAME,
            'description'     => self::defaultDescription(),
            'inLanguage'      => 'en-IN',
            'publisher'       => ['@id' => url('/#clinic')],
        ];
    }

    /** @return array<string,mixed> */
    public static function schemaPerson(): array
    {
        return Doctor::schemaPerson();
    }

    /** @return array<string,mixed> */
    public static function postalAddress(): array
    {
        $clinic = Clinic::info();

        return [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $clinic['address_full'],
            'addressLocality' => $clinic['city'],
            'addressRegion'   => $clinic['state'],
            'postalCode'      => $clinic['pin'],
            'addressCountry'  => $clinic['country_code'],
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public static function openingHours(): array
    {
        $specs = [];

        foreach (Clinic::hours() as $day => $slot) {
            foreach ($slot['periods'] as $period) {
                $specs[] = [
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'https://schema.org/' . ucfirst($day),
                    'opens'     => $period['open'],
                    'closes'    => $period['close'],
                ];
            }
        }

        return $specs;
    }

    /** @return string[] */
    public static function sameAs(): array
    {
        $links = array_filter([
            Config::string('clinic.facebook', ''),
            Config::string('clinic.instagram', ''),
            Config::string('clinic.x', ''),
            Config::string('clinic.youtube', ''),
        ]);

        return array_values($links);
    }

    /**
     * FAQPage graph. Accepts rows from the database or the default content set.
     *
     * @param array<int,array{question:string,answer:string}> $faqs
     * @return array<string,mixed>
     */
    public static function faqPage(array $faqs): array
    {
        $entities = [];
        foreach ($faqs as $faq) {
            $question = trim((string) ($faq['question'] ?? ''));
            $answer   = (string) ($faq['answer'] ?? '');
            if ($question === '' || $answer === '') {
                continue;
            }
            $entities[] = [
                '@type'          => 'Question',
                'name'           => strip_tags($question),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => trim(preg_replace('/\s+/u', ' ', strip_tags($answer)) ?? ''),
                ],
            ];
        }

        return [
            '@type'      => 'FAQPage',
            '@id'        => url('/faq#faqpage'),
            'mainEntity' => $entities,
        ];
    }

    /**
     * @param array<int,array{name:string,url?:string}> $trail Full trail starting at Home.
     * @return array<string,mixed>
     */
    public static function breadcrumb(array $trail): array
    {
        $elements = [];
        $position = 1;

        foreach ($trail as $item) {
            $itemUrl = (string) ($item['url'] ?? '');
            $entry   = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => strip_tags((string) $item['name']),
            ];
            if ($itemUrl !== '') {
                $entry['item'] = url($itemUrl);
            }
            $elements[] = $entry;
        }

        return [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * Wrap fragments in a single @graph document.
     *
     * @param array<int,array<string,mixed>> $fragments
     * @return array<string,mixed>
     */
    public static function graph(array $fragments): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph'   => array_values(array_filter($fragments)),
        ];
    }

    /**
     * Sitemap entries for every public route.
     *
     * @return array<int,array{loc:string}>
     */
    public static function sitemapEntries(): array
    {
        $entries = [
            ['loc' => url('/')],
            ['loc' => url('/about-us')],
            ['loc' => url('/faq')],
            ['loc' => url('/book-consultation')],
            ['loc' => url('/book-follow-up')],
            ['loc' => url('/contact')],
            ['loc' => url('/privacy-policy')],
        ];


        return $entries;
    }
}
