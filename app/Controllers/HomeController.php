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

        $this->setSeo([
            'title'       => Clinic::HEADLINE . ' — ' . Clinic::SERVICE_LINE . ' in ' . Clinic::LOCALITY,
            'description' => truncate(Clinic::intro(), 300, ''),
            'canonical'   => url('/'),
        ]);

        return $this->view('pages/home', [
            'page'         => 'home',
            'clinic'       => Clinic::info(),
            'doctor'       => Doctor::profile(),
            'pillars'      => Clinic::pillars(),
            'process'      => Clinic::process(),
            'testimonials' => $featured,
            'faqs'         => array_slice($faqs, 0, 6),
            'jsonLd'       => Seo::graph([
                Seo::medicalClinic(),
                Seo::webSite(),
                Seo::localBusiness(),
            ]),
        ]);
    }
}
