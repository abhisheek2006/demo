<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\Clinic;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Faq;
use App\Services\Seo;

/**
 * Public FAQ page and the /faqs.json feed consumed by the accordion search.
 */
final class FaqController extends Controller
{
    public function index(): Response
    {
        $grouped = Faq::grouped();

        $this->setSeo([
            'title'       => 'Frequently Asked Questions — Homoeopathy in Port Blair | ' . Clinic::NAME,
            'description' => 'Straight answers about homoeopathy treatment, consultation fees, appointment '
                . 'booking, children, pregnancy and emergencies at ' . Clinic::NAME . ' in Port Blair.',
            'canonical'   => url('/faq'),
        ]);

        return $this->view('pages/faq', [
            'page'   => 'faq',
            'clinic' => Clinic::info(),
            'grouped'=> $grouped,
            'total'  => array_sum(array_map('count', $grouped)),
            'jsonLd' => Seo::graph([
                Seo::faqPage(Faq::forSchema()),
                Seo::medicalClinic(),
                Seo::breadcrumb([
                    ['name' => 'Home', 'url' => '/'],
                    ['name' => 'FAQ', 'url' => '/faq'],
                ]),
            ]),
        ]);
    }

    public function feed(): Response
    {
        $categories = $this->request->string('category');
        $rows       = Faq::published($categories !== '' ? $categories : null);

        $payload = [
            'ok'    => true,
            'count' => count($rows),
            'faqs'  => array_map(static fn (array $r): array => [
                'id'       => (int) ($r['id'] ?? 0),
                'question' => (string) $r['question'],
                'answer'   => strip_tags((string) $r['answer']),
                'category' => (string) ($r['category'] ?? 'General'),
            ], $rows),
        ];

        return (new Response())
            ->json($payload)
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->cache(3600);
    }
}
