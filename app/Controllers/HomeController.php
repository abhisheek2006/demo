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
<<<<<<< Updated upstream
        $faqs     = self::featuredFaqs(Faq::forSchema());
=======
        $faqs     = Faq::forSchema();
        $faqLookup = [];
        foreach ($faqs as $faq) {
            $faqLookup[mb_strtolower(trim((string) $faq['question']))] = $faq;
        }
        foreach (FaqData::all() as $faq) {
            $faqLookup[mb_strtolower(trim((string) $faq['question']))] ??= [
                'question' => (string) $faq['question'],
                'answer'   => (string) $faq['answer'],
            ];
        }

        $homeFaqs = [];
        $homeQuestions = [
            'Is homoeopathy safe?' => ['Is homoeopathy safe?', 'Is homoeopathy safe without side effects?'],
            'Can I take homoeopathic medicines alongside allopathic medication, or should I stop my current medicines?' => [
                'Can I take homoeopathic medicines alongside allopathic medication, or should I stop my current medicines?',
            ],
            'Can homoeopathy be given to infants and children?' => ['Can homoeopathy be given to infants and children?'],
            'What should I bring to my first appointment?' => ['What should I bring to my first appointment?'],
            'Where is Swasti Homoeo Clinic located?' => ['Where is Swasti Homoeo Clinic located?'],
        ];
        foreach ($homeQuestions as $displayQuestion => $lookupQuestions) {
            foreach ($lookupQuestions as $lookupQuestion) {
                $faq = $faqLookup[mb_strtolower($lookupQuestion)] ?? null;
                if ($faq !== null) {
                    $homeFaqs[] = ['question' => $displayQuestion, 'answer' => (string) $faq['answer']];
                    break;
                }
            }
        }
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
            'testimonials' => $testimonials,
            'faqs'         => $faqs,
=======
            'testimonials' => $featured,
            'faqs'         => $homeFaqs,
>>>>>>> Stashed changes
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
