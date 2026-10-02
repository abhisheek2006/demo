<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\ArticlesData;
use App\Content\Booking;
use App\Content\Clinic;
use App\Content\Doctor;
use App\Content\Services as ServiceContent;
use App\Content\Testimonials;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\Seo;

/**
 * About Us and the clinic's long-form informational pages.
 */
final class PageController extends Controller
{
    public function about(): Response
    {
        $this->setSeo([
            'title'       => 'About Us & Dr. Smriti Das, BHMS MD (WBUHS) | ' . Clinic::NAME,
            'description' => 'Meet Dr. Smriti Das, the homoeopathic physician behind ' . Clinic::NAME
                . ' in Port Blair. Eight years of classical homoeopathic practice, and an honest view of '
                . 'what this treatment can and cannot do.',
            'canonical'   => url('/about-us'),
            'image'       => url('/assets/images/og/og-about.png'),
        ]);

        return $this->view('pages/about', [
            'page'        => 'about',
            'clinic'      => Clinic::info(),
            'doctor'      => Doctor::profile(),
            'biography'   => Doctor::biography(),
            'credentials' => Doctor::credentials(),
            'languages'   => Doctor::languages(),
            'stats'       => Clinic::stats(),
            'pillars'     => Clinic::pillars(),
            'testimonials'=> Testimonials::all(),
            'services'    => array_slice(ServiceContent::all(), 0, 4),
            'jsonLd'      => Seo::graph([
                Seo::medicalClinic(),
                Seo::schemaPerson(),
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'About Us', 'url' => '/about-us'],
                ]),
            ]),
        ]);
    }

    public function services(): Response
    {
        $this->setSeo([
            'title'       => 'Services — Classical Homoeopathic Treatment in Port Blair',
            'description' => 'Consultation, follow-up review, paediatric care, skin and allergy clinic, '
                . 'women’s health, musculoskeletal and digestive care at ' . Clinic::NAME . '.',
            'canonical'   => url('/services'),
        ]);

        return $this->view('pages/services', [
            'page'            => 'services',
            'clinic'          => Clinic::info(),
            'services'        => ServiceContent::all(),
            'conditionGroups' => ServiceContent::conditionGroups(),
            'scopeNote'       => ServiceContent::scopeNote(),
            'process'         => Clinic::process(),
            'emergency'       => Clinic::emergencyGuidance(),
            'jsonLd'          => Seo::graph([
                Seo::medicalClinic(),
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Services', 'url' => '/services'],
                ]),
            ]),
        ]);
    }

    public function contact(): Response
    {
        $this->setSeo([
            'title'       => 'Contact & Directions — ' . Clinic::NAME . ', Port Blair',
            'description' => 'Address, phone, WhatsApp and opening hours for ' . Clinic::NAME . ', '
                . 'Solar Colony, Bhathu Basti, Port Blair 744105. Directions and parking notes.',
            'canonical'   => url('/contact'),
        ]);

        return $this->view('pages/contact', [
            'page'        => 'contact',
            'clinic'      => Clinic::info(),
            'hours'       => Clinic::hours(),
            'addressLines'=> Clinic::addressLines(),
            'emergency'   => Clinic::emergencyGuidance(),
            'jsonLd'      => Seo::graph([
                Seo::medicalClinic(),
                Seo::localBusiness(),
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Contact', 'url' => '/contact'],
                ]),
            ]),
        ]);
    }

    public function privacy(): Response
    {
        $this->setSeo([
            'title'       => 'Privacy Policy — ' . Clinic::NAME,
            'description' => 'How ' . Clinic::NAME . ' collects, uses, stores and protects the health '
                . 'information you submit through this website.',
            'canonical'   => url('/privacy-policy'),
        ]);

        return $this->view('pages/privacy', [
            'page'            => 'privacy',
            'clinic'          => Clinic::info(),
            'privacySections' => Clinic::privacySections(),
            'updated'         => date('1 F Y'),
        ]);
    }

    public function articles(): Response
    {
        $this->setSeo([
            'title'       => 'Health Articles — Homoeopathy and Family Health | ' . Clinic::NAME,
            'description' => 'Practical, plain-language health guides written by the team at '
                . Clinic::NAME . ' for families in Port Blair and the Andaman Islands.',
            'canonical'   => url('/articles'),
        ]);

        return $this->view('pages/articles', [
            'page'     => 'articles',
            'clinic'   => Clinic::info(),
            'articles' => ArticlesData::all(),
            'jsonLd'   => Seo::graph([
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Articles', 'url' => '/articles'],
                ]),
            ]),
        ]);
    }

    public function article(string $slug = ''): Response
    {
        $article = $slug === '' ? null : ArticlesData::findBySlug($slug);

        if ($article === null) {
            $this->abort(404, 'Article not found');
        }

        /** @var array<string,mixed> $article */
        $related = array_values(array_filter(
            ArticlesData::all(),
            static fn (array $a): bool => $a['slug'] !== $article['slug']
        ));

        $this->setSeo([
            'title'         => $article['meta_title'] ?? $article['title'],
            'description'   => $article['meta_description'] ?? $article['excerpt'],
            'canonical'     => url('/articles/' . $article['slug']),
            'type'          => 'article',
            'image'         => url($article['image']),
            'published_time'=> date('c', strtotime('2026-01-15')),
        ]);

        return $this->view('pages/article', [
            'page'     => 'articles',
            'clinic'   => Clinic::info(),
            'article'  => $article,
            'related'  => array_slice($related, 0, 3),
            'booking'  => Booking::preparation(),
            'jsonLd'   => Seo::graph([
                [
                    '@type'         => 'Article',
                    'headline'      => $article['title'],
                    'description'   => $article['excerpt'],
                    'image'         => url($article['image']),
                    'datePublished' => date('c', strtotime('2026-01-15')),
                    'dateModified'  => date('c'),
                    'author'        => ['@type' => 'Physician', 'name' => Doctor::profile()['name']],
                    'publisher'     => ['@id' => url('/#clinic')],
                    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url('/articles/' . $article['slug'])],
                ],
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'Articles', 'url' => '/articles'],
                    ['name' => $article['title'], 'url' => '/articles/' . $article['slug']],
                ]),
            ]),
        ]);
    }
}
