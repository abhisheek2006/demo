<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Content\Clinic;
use App\Content\Doctor;
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
        $faqs     = Faq::forSchema();
        $featured = Testimonials::featured();
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
            'testimonials' => $featured,
            'faqs'         => array_slice($faqs, 0, 6),
            'jsonLd'       => Seo::graph([
                Seo::medicalClinic(),
                Seo::webSite(),
                Seo::schemaPerson(),
            ]),
        ]);
    }
}
