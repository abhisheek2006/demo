<?php
declare(strict_types=1);

namespace App\Content;

/**
 * Services offered by the clinic. Rendered on the home page, the services
 * page, and as Service nodes in the site's structured data.
 */
final class Services
{
    /**
     * @return array<int,array{
     *     id:string,title:string,tagline:string,icon:string,image:string,
     *     intro:string,includes:string[],suitable_for:string,note:string
     * }>
     */
    public static function all(): array
    {
        return [
            [
                'id'          => 'general-consultation',
                'title'       => 'General Homoeopathic Consultation',
                'tagline'     => 'The starting point for most patients',
                'icon'        => 'stethoscope',
                'image'       => '/assets/images/features/service-consultation.svg',
                'intro'       => 'A full case history and physical examination, followed by an '
                    . 'individualised prescription. This is the appropriate appointment if you have a '
                    . 'new complaint, several symptoms at once, or you have never been assessed here '
                    . 'before.',
                'includes'    => [
                    'Detailed history of present, past and family illness',
                    'General physical examination and vital observations',
                    'Review of any reports, prescriptions or medicines you already take',
                    'Individualised homoeopathic remedy with potency and dose',
                    'Written instructions covering diet, rest and activity',
                    'A clear follow-up date and what to watch for before then',
                ],
                'suitable_for' => 'First visits, new complaints, second opinions on an existing prescription.',
                'note'        => 'Duration is typically 30 to 45 minutes. Please bring your current '
                    . 'medicine bottles and any investigation reports.',
            ],
            [
                'id'          => 'follow-up-review',
                'title'       => 'Follow-Up Review',
                'tagline'     => 'Short, focused reassessment',
                'icon'        => 'refresh',
                'image'       => '/assets/images/features/service-follow-up.svg',
                'intro'       => 'A brief appointment to check how the current prescription is working, '
                    . 'whether symptoms have changed, and whether the plan needs to continue unchanged, '
                    . 'be reduced, or be replaced.',
                'includes'    => [
                    'Symptom change assessment since the last visit',
                    'Adherence check on dose, timing and repetition',
                    'Re-examination where clinically indicated',
                    'Continued, reduced or changed prescription',
                    'Updated written instructions and next review date',
                ],
                'suitable_for' => 'Patients already under treatment here, at 2, 4 or 6-week intervals.',
                'note'        => 'Duration is typically 10 to 15 minutes. Book this, not a new '
                    . 'consultation, if you are already on a prescription from us.',
            ],
            [
                'id'          => 'child-and-fever',
                'title'       => 'Paediatric & Acute Care',
                'tagline'     => 'Gentle treatment for infants and children',
                'icon'        => 'child',
                'image'       => '/assets/images/features/service-paediatric.svg',
                'intro'       => 'Assessment of common childhood complaints — fever, cough and cold, '
                    . 'teething problems, colic, poor appetite, loose motions, recurrent infections and '
                    . 'eczema — with potency and dose chosen for the child\'s age and sensitivity.',
                'includes'    => [
                    'Age-appropriate history taken from the parent or attendant',
                    'Weight-based dosing and gentle administration guidance',
                    'Feeding, sleep and hydration advice for the child',
                    'Warning signs explained clearly to the parent',
                    'Remedy dispensed with written dosing schedule',
                    'Clear instruction on when to return or go to hospital',
                ],
                'suitable_for' => 'Infants, toddlers and school-age children with acute or recurrent complaints.',
                'note'        => 'High fever, breathing difficulty, drowsiness, refusal to feed, '
                    . 'dehydration or a child who is simply not themselves are reasons to go to a '
                    . 'hospital immediately. We will say so.',
            ],
            [
                'id'          => 'skin-hair',
                'title'       => 'Skin, Hair & Allergy Clinic',
                'tagline'     => 'Long-term, patience-based care',
                'icon'        => 'leaf',
                'image'       => '/assets/images/features/service-skin.svg',
                'intro'       => 'Homoeopathy has a long record with eczema, psoriasis, acne, urticaria, '
                    . 'allergic rhinitis and recurring skin infections, treated by addressing the '
                    . 'underlying tendency rather than by suppressing each flare.',
                'includes'    => [
                    'Skin and scalp examination with photographic progress notes',
                    'Identification of triggers, allergens and aggravating habits',
                    'Internal remedy plus topical and lifestyle measures',
                    'Guidance on creams, soaps, detergents and diet',
                    'Planned review schedule across flare-free periods',
                ],
                'suitable_for' => 'Eczema, psoriasis, acne, boils, urticaria, allergic rhinitis, alopecia.',
                'note'        => 'Skin conditions need time. A realistic timeline is agreed at the '
                    . 'first visit, and any suspicious mole or lesion is referred for dermatology review.',
            ],
            [
                'id'          => 'women-health',
                'title'       => 'Women’s Health',
                'tagline'     => 'Menstrual, hormonal and fertility support',
                'icon'        => 'heart',
                'image'       => '/assets/images/features/service-women.svg',
                'intro'       => 'Care for irregular and painful menstruation, PMS, menopausal symptoms, '
                    . 'recurrent urinary and vaginal infections, and fertility-related concerns, '
                    . 'coordinated with whatever gynaecological care you already have.',
                'includes'    => [
                    'Detailed menstrual and hormonal history',
                    'Cycle-tracking guidance with a written record sheet',
                    'Individualised remedy for the complaint pattern',
                    'Diet, sleep and stress-management advice',
                    'Coordination with gynaecology or fertility specialist where needed',
                ],
                'suitable_for' => 'Adolescents, women planning pregnancy, and those in menopause.',
                'note'        => 'Pregnancy is treated only after confirmation by a qualified obstetrician, '
                    . 'and allopathic medication is never stopped without its prescriber’s advice.',
            ],
            [
                'id'          => 'bone-joint',
                'title'       => 'Musculoskeletal & Joint Pain',
                'tagline'     => 'Back, knee, neck and sports injuries',
                'icon'        => 'activity',
                'image'       => '/assets/images/features/service-joint.svg',
                'intro'       => 'Management of lower back pain, cervical and lumbar spondylosis, knee '
                    . 'osteoarthritis, frozen shoulder, tendon strains and post-injury stiffness, '
                    . 'combined with practical physiotherapy advice.',
                'includes'    => [
                    'Pain mapping, range-of-motion and functional assessment',
                    'Remedy for the underlying muscular and constitutional picture',
                    'Physiotherapy, stretching and posture guidance',
                    'Ergonomic and activity modification advice',
                    'Safe, staged return-to-activity plan',
                ],
                'suitable_for' => 'Back and neck pain, arthritis, repetitive strain, sports injuries.',
                'note'        => 'A recent major fall, loss of bladder or bowel control, numbness in the '
                    . 'saddle area, or severe night pain needs an orthopaedic or surgical assessment first.',
            ],
            [
                'id'          => 'digestive-metabolic',
                'title'       => 'Digestive & Metabolic Health',
                'tagline'     => 'Acidity, gastritis, liver and weight support',
                'icon'        => 'flask',
                'image'       => '/assets/images/features/service-digestive.svg',
                'intro'       => 'Homoeopathic treatment for acidity and gastritis, indigestion, '
                    . 'constipation, irritable bowel symptoms, poor appetite, and lifestyle-related '
                    . 'weight and lipid concerns, with realistic expectations about results.',
                'includes'    => [
                    'Dietary diary review and trigger identification',
                    'Remedy selected for the digestive totality',
                    'Structured eating and hydration plan',
                    'Weight, sleep and stress coaching',
                    'Monitoring of blood sugar and lipid reports when available',
                ],
                'suitable_for' => 'Acidity, gastritis, IBS-type symptoms, obesity, high cholesterol.',
                'note'        => 'Difficulty or pain swallowing, vomiting blood, black stools, unexplained '
                    . 'weight loss or jaundice require immediate medical evaluation and investigation.',
            ],
            [
                'id'          => 'anxiety-sleep',
                'title'       => 'Anxiety, Sleep & Stress',
                'tagline'     => 'Support for an overactive nervous system',
                'icon'        => 'moon',
                'image'       => '/assets/images/features/service-mind.svg',
                'intro'       => 'Support for anxiety, irritability, poor sleep, panic episodes, exam '
                    . 'and work stress, and exhaustion — always alongside, never instead of, '
                    . 'psychiatric or psychological care where that is needed.',
                'includes'    => [
                    'Symptom and trigger diary',
                    'Remedy for the constitutional and emotional picture',
                    'Structured sleep and wind-down routine',
                    'Breathing, grounding and simple relaxation practice',
                    'Referral to a psychiatrist or psychologist where indicated',
                ],
                'suitable_for' => 'Anxiety, insomnia, burnout, panic, irritability, low mood.',
                'note'        => 'Suicidal thoughts, severe depression, hallucinations, or any risk of '
                    . 'harm to yourself or others require immediate professional crisis support, not a '
                    . 'homeopathic appointment. Andaman helpline: 181 (Tele-MANAS) or 112.',
            ],
        ];
    }

    /** @return array<int,string> */
    public static function titles(): array
    {
        return array_column(self::all(), 'title');
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $service) {
            if ($service['id'] === $id) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Conditions commonly presented at the clinic, grouped. Used on the
     * services page and the home page condition cloud.
     *
     * @return array<string,string[]>
     */
    public static function conditionGroups(): array
    {
        return [
            'Fever & infections' => [
                'Fever and flu-like illness', 'Cough, cold and bronchitis', 'Sinusitis and congestion',
                'Tonsillitis and sore throat', 'Recurrent infections in children', 'Typhoid convalescence',
            ],
            'Skin and allergy' => [
                'Eczema and dermatitis', 'Acne and pimples', 'Psoriasis', 'Urticaria and skin allergy',
                'Allergic rhinitis and dust allergy', 'Hair fall and dandruff', 'Boils and abscesses',
            ],
            'Digestive' => [
                'Acidity and gastritis', 'Indigestion and gas', 'Constipation', 'Irritable bowel symptoms',
                'Piles and fissures', 'Liver complaints', 'Poor appetite in children',
            ],
            'Women’s health' => [
                'Irregular menstruation', 'Painful periods', 'PMS and mood changes', 'Menopausal symptoms',
                'Recurrent urinary infection', 'White discharge and vaginal infection', 'Fertility support',
            ],
            'Bones, joints and pain' => [
                'Lower back pain', 'Cervical and lumbar spondylosis', 'Knee pain and arthritis',
                'Frozen shoulder', 'Muscle strain and sports injury', 'Heel pain', 'Gout tendency',
            ],
            'Mind, sleep and nerves' => [
                'Anxiety and restlessness', 'Insomnia', 'Exam and work stress', 'Irritability and anger',
                'Panic attacks', 'Mental fatigue and burnout',
            ],
            'Metabolic and lifestyle' => [
                'Weight management', 'High blood pressure tendency', 'High cholesterol', 'Melasma',
                'Sleep apnea support', 'Low immunity and debility',
            ],
        ];
    }

    /**
     * Honest scope note. Displayed on the services page and in the FAQ.
     */
    public static function scopeNote(): string
    {
        return 'Homoeopathy is best used as complementary care alongside appropriate medical '
            . 'management — never instead of emergency treatment, surgery, antibiotics where '
            . 'clinically indicated, immunisation, or any medicine your doctor has already '
            . 'prescribed. Where a case needs hospital care, we will tell you plainly and refer you.';
    }

    /**
     * @return array<int,array{id:string,title:string}>
     */
    public static function schemaGraph(): array
    {
        $out = [];
        foreach (self::all() as $service) {
            $out[] = [
                '@type'    => 'MedicalTherapy',
                'name'     => $service['title'],
                'url'      => url('/services#' . $service['id']),
                'image'    => url($service['image']),
                'description' => truncate($service['intro'], 300),
                'provider' => [
                    '@type' => 'MedicalClinic',
                    'name'  => Clinic::NAME,
                ],
            ];
        }

        return $out;
    }
}
