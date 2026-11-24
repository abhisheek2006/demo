<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\Clinic;
use App\Content\Doctor;
use App\Content\FaqData;
use App\Content\Testimonials;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Faq;
use App\Services\Seo;

/**
 * Home page.
 */
final class HomeController extends Controller
{
    public function index(): Response
    {
        $faqs     = self::featuredFaqs(Faq::forSchema());
        $featured = Testimonials::featured();
        $featuredNames = array_column($featured, 'name');
        $remainingTestimonials = array_values(array_filter(
            Testimonials::all(),
            static fn (array $testimonial): bool => !in_array($testimonial['name'], $featuredNames, true)
        ));
        $testimonials = array_merge($featured, $remainingTestimonials);
        $clinic   = Clinic::info();
        $doctor   = Doctor::profile();

        $this->setSeo([
            'title'       => Clinic::HEADLINE,
            'description' => $clinic['name'] . ' in Garacharma, ' . $clinic['city'] . ' offers classical homoeopathic consultations with '
                . $doctor['name'] . ' (' . $doctor['short_qualification'] . '). Call ' . format_phone() . ' to book.',
            'canonical'   => url('/'),
        ]);

        return $this->view('pages/home', [
            'page'         => 'home',
            'clinic'       => $clinic,
            'doctor'       => $doctor,
            'pillars'      => array_slice(Clinic::pillars(), 0, 2),
            'testimonials' => $testimonials,
            'faqs'         => $faqs,
            'jsonLd'       => Seo::graph([
                Seo::medicalClinic(),
                Seo::webSite(),
                Seo::schemaPerson(),
            ]),
        ]);
    }

    /** @param array<int,array{question:string,answer:string}> $rows
     *  @return array<int,array{question:string,answer:string}> */
    private static function featuredFaqs(array $rows): array
    {
        $fallback = [];
        foreach (FaqData::all() as $row) {
            $fallback[] = ['question' => (string) $row['question'], 'answer' => (string) $row['answer']];
        }

        $wanted = [
            [
                'question' => 'Is homoeopathy safe?',
                'matches'  => ['is homoeopathy safe'],
            ],
            [
                'question' => 'Can I take homoeopathic medicines alongside allopathic medication, or should I stop my current medicines?',
                'matches'  => [
                    'can i take homoeopathic medicines alongside allopathic medication',
                    'can i take homoeopathic medicines with allopathic medicines',
                    'can i stop my allopathic medicines',
                ],
            ],
            [
                'question' => 'Can homoeopathy be given to infants and children?',
                'matches'  => ['can homoeopathy be given to infants and children'],
            ],
            [
                'question' => 'What should I bring to my first appointment?',
                'matches'  => ['what should i bring to my first appointment'],
            ],
            [
                'question' => 'Where is Swasti Homoeo Clinic located?',
                'matches'  => ['where is swasti homoeo clinic located'],
            ],
        ];

        $featured = [];
        foreach ($wanted as $item) {
            $answer = '';
            foreach (array_merge($rows, $fallback) as $row) {
                $sourceQuestion = mb_strtolower(trim($row['question']));
                foreach ($item['matches'] as $match) {
                    if (str_contains($sourceQuestion, $match)) {
                        $answer = trim($row['answer']);
                        break 2;
                    }
                }
            }

            if ($answer !== '') {
                $featured[] = ['question' => $item['question'], 'answer' => $answer];
            }
        }

        return $featured;
    }
}
