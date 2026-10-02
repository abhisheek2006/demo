<?php
declare(strict_types=1);

namespace App\Controllers;

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


}
