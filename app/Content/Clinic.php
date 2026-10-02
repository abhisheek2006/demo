<?php
declare(strict_types=1);

namespace App\Content;

use App\Core\Config;

/**
 * Single source of truth for clinic identity, contact details, navigation and
 * structural copy. Every value can be overridden from .env for the contact
 * fields so the deployed site can be re-pointed without a code change.
 */
final class Clinic
{
    public const NAME            = 'Swasti Homoeo Clinic';
    public const LEGAL_NAME      = 'Andaman Homoeo Health Care LLP';
    public const TAGLINE         = 'We believe in easy, safe and quick recovery';
    public const SERVICE_LINE    = 'Classical Homoeopathic Treatment';
    public const HEADLINE        = 'Best Homoeopathy Clinic in Port Blair';
    public const ESTABLISHED     = '2017';
    public const LOCALITY        = 'Garacharma, Port Blair';

    /**
     * @return array<string,string>
     */
    public static function info(): array
    {
        return [
            'name'         => self::NAME,
            'legal_name'   => self::LEGAL_NAME,
            'tagline'      => self::TAGLINE,
            'service_line' => self::SERVICE_LINE,
            'headline'     => self::HEADLINE,
            'established'  => self::ESTABLISHED,
            'locality'     => self::LOCALITY,
            'phone'        => Config::string('clinic.phone', '+917980644867'),
            'whatsapp'     => Config::string('clinic.whatsapp', '917980644867'),
            'email'        => Config::string('clinic.email', 'info@swastihomeo.com'),
            'facebook'     => Config::string('clinic.facebook', ''),
            'instagram'    => Config::string('clinic.instagram', ''),
            'x'            => Config::string('clinic.x', ''),
            'youtube'      => Config::string('clinic.youtube', ''),
            'address'      => self::addressLines(),
            'address_full' => self::addressOneLine(),
            'city'         => 'Port Blair',
            'state'        => 'Andaman and Nicobar Islands',
            'country'      => 'India',
            'country_code' => 'IN',
            'pin'          => '744105',
            'timezone'     => 'Asia/Kolkata',
            'geo_lat'      => '11.6234',
            'geo_lng'      => '92.7265',
        ];
    }

    /** @return string[] */
    public static function addressLines(): array
    {
        return [
            'Solar Colony, Solar Plant Road',
            'Above Bala Dental Clinic',
            "Opposite Tulasi's Diagnostic Centre",
            'Housing Colony, Bhathu Basti',
            'Port Blair, Andaman & Nicobar Islands 744105',
        ];
    }

    public static function addressOneLine(): string
    {
        return 'Solar Colony, Solar Plant Road, above Bala Dental Clinic, opposite Tulasi’s '
            . 'Diagnostic Centre, Housing Colony, Bhathu Basti, Port Blair, Andaman & Nicobar '
            . 'Islands 744105';
    }

    /**
     * Google Maps search link for the clinic, used by the footer location icon.
     */
    public static function mapUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode(self::addressOneLine());
    }

    /**
     * Primary navigation used by the header and the mobile drawer.
     *
     * @return array<int,array{label:string,url:string,match:string}>
     */
    public static function navigation(): array
    {
        return [
            ['label' => 'Home',           'url' => '/',                  'match' => '/'],
            ['label' => 'About Us',       'url' => '/about-us',           'match' => '/about-us'],
            ['label' => 'FAQ',            'url' => '/faq',               'match' => '/faq'],
            ['label' => 'Contact',        'url' => '/contact',           'match' => '/contact'],
        ];
    }

    /**
     * Footer navigation split into columns.
     *
     * @return array<string,array<int,array{label:string,url:string}>>
     */
    public static function footerNavigation(): array
    {
        return [
            'Appointments' => [
                ['label' => 'Book Consultation', 'url' => '/book-consultation'],
                ['label' => 'Book Follow-Up',    'url' => '/book-follow-up'],
            ],
            'Clinic' => [
                ['label' => 'About the Doctor',     'url' => '/about-us#doctor'],
                ['label' => 'Our Approach',         'url' => '/about-us#approach'],
                ['label' => 'Patient Stories',      'url' => '/about-us#testimonials'],
                ['label' => 'Contact & Directions', 'url' => '/contact'],
            ],
            'Support' => [
                ['label' => 'Frequently Asked',     'url' => '/faq'],
                ['label' => 'Privacy Policy',       'url' => '/privacy-policy'],
            ],
        ];
    }

    /**
     * Opening hours. Keys are lowercase English day names.
     *
     * @return array<string,array{open:string,close:string,closed?:bool,note?:string}>
     */
    public static function hours(): array
    {
        return [
            'monday'    => ['open' => '10:00', 'close' => '19:00', 'note' => 'Morning and evening OPD'],
            'tuesday'   => ['open' => '10:00', 'close' => '19:00', 'note' => 'Morning and evening OPD'],
            'wednesday' => ['open' => '10:00', 'close' => '19:00', 'note' => 'Morning and evening OPD'],
            'thursday'  => ['open' => '10:00', 'close' => '19:00', 'note' => 'Morning and evening OPD'],
            'friday'    => ['open' => '10:00', 'close' => '19:00', 'note' => 'Morning and evening OPD'],
            'saturday'  => ['open' => '10:00', 'close' => '15:00', 'note' => 'Morning OPD only'],
            'sunday'    => ['open' => '10:00', 'close' => '12:00', 'closed' => true, 'note' => 'Emergency calls only'],
        ];
    }

    /** @return array<int,array{day:string,open:string,close:string,note:string}> */
    public static function hoursForSchema(): array
    {
        $out = [];
        foreach (self::hours() as $day => $slot) {
            $out[] = [
                'day'   => ucfirst($day),
                'open'  => $slot['open'],
                'close' => $slot['close'],
                'note'  => $slot['note'] ?? '',
            ];
        }

        return $out;
    }

    /**
     * Statistic tiles used on the home page and in structured data.
     *
     * @return array<int,array{value:string,label:string,icon:string}>
     */
    public static function stats(): array
    {
        return [
            ['value' => '8+',    'label' => 'Years of clinical practice', 'icon' => 'stethoscope'],
            ['value' => '6,000+','label' => 'Consultations completed',      'icon' => 'users'],
            ['value' => '40+',   'label' => 'Conditions treated',          'icon' => 'leaf'],
            ['value' => '12',    'label' => 'Remedies in classical use',   'icon' => 'flask'],
        ];
    }

    /**
     * The three "why choose us" pillars.
     *
     * @return array<int,array{title:string,text:string,icon:string}>
     */
    public static function pillars(): array
    {
        return [
            [
                'icon'  => 'leaf',
                'title' => 'Classical homoeopathy, practised properly',
                'text'  => 'Every prescription is worked out from your own symptoms and history '
                    . 'using classical homoeopathic principles — never a fixed package deal for a '
                    . 'named disease. Remedies are prepared in a licensed pharmacy and dispensed '
                    . 'with clear written instructions.',
            ],
            [
                'icon'  => 'shield',
                'title' => 'Safe for the whole family',
                'text'  => 'Homoeopathic medicines are prescribed in potency and dose appropriate to '
                    . 'age and sensitivity, which is why they can be used for infants, pregnancy, '
                    . 'seniors and people managing long-term conditions. No antibiotics, no steroids, '
                    . 'no sedatives hidden in the prescription.',
            ],
            [
                'icon'  => 'clock',
                'title' => 'Easy, safe and quick recovery',
                'text'  => 'We keep the process simple: book a slot, come in for a proper history, '
                    . 'leave with a written plan. Follow-up reviews are short, focused and free of '
                    . 'pressure, so treatment stays affordable and realistic.',
            ],
        ];
    }

    /**
     * The clinic's treatment process, used on the home page and about page.
     *
     * @return array<int,array{step:string,title:string,text:string}>
     */
    public static function process(): array
    {
        return [
            [
                'step'  => '01',
                'title' => 'Book your slot',
                'text'  => 'Send a consultation request online, or call or WhatsApp the clinic directly. '
                    . 'We confirm a convenient morning or evening time.',
            ],
            [
                'step'  => '02',
                'title' => 'Detailed case history',
                'text'  => 'Dr. Smriti Das takes a full history — symptoms, timeline, sleep, digestion, '
                    . 'stress, past illness and family tendency — before selecting any remedy.',
            ],
            [
                'step'  => '03',
                'title' => 'Remedy and written plan',
                'text'  => 'You receive a dispensed remedy with written instructions on dose, timing and '
                    . 'diet. Nothing is withheld and nothing is added that you have not asked for.',
            ],
            [
                'step'  => '04',
                'title' => 'Follow-up and review',
                'text'  => 'A short review appointment tracks your progress and the prescription is '
                    . 'reworked if your symptoms have changed.',
            ],
        ];
    }

    /**
     * Emergency and out-of-scope guidance. Shown on booking and contact pages
     * so visitors know when not to wait for a homoeopathic appointment.
     *
     * @return array<int,string>
     */
    public static function emergencyGuidance(): array
    {
        return [
            'Chest pain, breathing difficulty, sudden weakness, facial droop, heavy bleeding, loss of '
            . 'consciousness, severe burns, a severe allergic reaction, or any injury from an accident '
            . 'are emergencies.',
            'For an emergency, call 112 or take the patient to the nearest emergency department '
            . 'immediately. Do not wait for a homoeopathic appointment.',
            'Homoeopathic treatment is not a substitute for emergency care, surgery, antibiotics where '
            . 'clinically indicated, or any treatment your doctor has already prescribed. We will '
            . 'always tell you when a case needs hospital care.',
            'If you are already on medication for diabetes, blood pressure, thyroid, epilepsy, heart '
            . 'disease, mental health conditions or pregnancy, please continue it and tell the doctor.',
        ];
    }

    /**
     * Short positioning statement used for SEO descriptions and the intro band.
     */
    public static function intro(): string
    {
        return 'Swasti Homoeo Clinic is a classical homoeopathy practice in Bhathu Basti, Port Blair, '
            . 'led by Dr. Smriti Das (BHMS, MD — WBUHS) with more than eight years of clinical '
            . 'experience. We believe in easy, safe and quick recovery — and that means honest '
            . 'assessment, careful prescribing and a plan you can actually follow.';
    }

    /**
     * Privacy policy sections (revision date is derived at render time).
     *
     * @return array<int,array{title:string,body:string}>
     */
    public static function privacySections(): array
    {
        return [
            [
                'title' => 'Who we are',
                'body'  => 'Swasti Homoeo Clinic ("we", "us") is operated by ' . self::LEGAL_NAME . ', '
                    . 'a Limited Liability Partnership registered in Port Blair, Andaman and Nicobar '
                    . 'Islands, India. For any question about this policy or your health information, '
                    . 'contact us at ' . Config::string('clinic.email', 'info@swastihomeo.com') . '.',
            ],
            [
                'title' => 'What we collect',
                'body'  => 'When you submit a consultation, follow-up or contact form we collect only '
                    . 'what is needed to help you: your name, phone number, optional email address, '
                    . 'age, the problem you want help with, your preferred date and time, and anything '
                    . 'else you choose to write. Our servers also record the IP address and browser '
                    . 'used when the form was submitted, which we use only for spam prevention and '
                    . 'security auditing.',
            ],
            [
                'title' => 'How we use your information',
                'body'  => 'Your details are used to contact you about your appointment, to prepare for '
                    . 'your consultation, to send the prescription and dosage instructions, and to '
                    . 'send a short reminder before your visit. If you ask us in writing, we will '
                    . 'remove your enquiry record from our system. We do not sell, rent or share your '
                    . 'health information with advertisers, data brokers or third-party marketers.',
            ],
            [
                'title' => 'How your information is stored',
                'body'  => 'Consultation details are stored in our own clinic database, on servers '
                    . 'hosted by our web hosting provider in India, protected by access controls and '
                    . 'encrypted in transit over HTTPS. Paper records, where kept, are stored in a '
                    . 'locked cabinet at the clinic. We retain enquiry details only as long as needed '
                    . 'for your care and our legal obligations.',
            ],
            [
                'title' => 'Sharing, only when necessary',
                'body'  => 'We share your information only with a hospital or laboratory if we believe '
                    . 'your condition requires it, with your written consent, and with our email and '
                    . 'web hosting providers purely to deliver the service. We may disclose information '
                    . 'where the law or a court order requires it.',
            ],
            [
                'title' => 'Cookies',
                'body'  => 'This website uses a single first-party session cookie that keeps you signed '
                    . 'in to the clinic admin area and protects forms against cross-site request '
                    . 'forgery. It contains no tracking identifier and is not shared with anyone. We do '
                    . 'not run advertising, analytics or cross-site tracking cookies.',
            ],
            [
                'title' => 'Your rights and choices',
                'body'  => 'You may ask to see the information we hold about you, ask us to correct it, '
                    . 'or ask us to delete it. You can also decline to be contacted for reminders, or '
                    . 'stop using the forms and simply call the clinic instead. Write to us and we will '
                    . 'respond within seven working days.',
            ],
            [
                'title' => 'Security',
                'body'  => 'The site is served over HTTPS, forms are protected with CSRF tokens, a honeypot '
                    . 'field, a minimum dwell-time check and rate limiting, and all output is escaped. '
                    . 'No online system is perfectly secure, so please do not send photographs or highly '
                    . 'sensitive personal details through the form — share those during your visit.',
            ],
            [
                'title' => 'Medical disclaimer',
                'body'  => 'Information on this website is provided for general awareness and is not a '
                    . 'diagnosis or a substitute for a consultation with a qualified doctor. Nothing here '
                    . 'is intended to replace advice from your treating physician, and no remedy should '
                    . 'be started, stopped or changed without speaking to a clinician.',
            ],
            [
                'title' => 'Changes to this policy',
                'body'  => 'We may revise this policy as the website or the law changes. The revision '
                    . 'date is shown at the top of this page, and the updated version will always be '
                    . 'available at /privacy-policy.',
            ],
        ];
    }
}
