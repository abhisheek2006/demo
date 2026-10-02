<?php
declare(strict_types=1);

namespace App\Content;

/**
 * Seed content for the `articles` table (rendered at /articles/{slug}).
 * The body uses a deliberately small HTML subset — <p>, <h3>, <h4>, <ul>,
 * <ol>, <li>, <strong>, <em> — which the narrative() helper preserves and
 * anything else is stripped from on render.
 */
final class ArticlesData
{
    /**
     * @return array<int,array{
     *   slug:string,title:string,excerpt:string,category:string,read_minutes:int,
     *   image:string,meta_title:string,meta_description:string,body:string
     * }>
     */
    public static function all(): array
    {
        return [
            [
                'slug'            => 'homoeopathy-for-recurrent-cold-in-children',
                'title'           => 'Recurrent Colds in Children: What Actually Helps',
                'category'        => 'Paediatric care',
                'read_minutes'    => 6,
                'image'           => '/assets/images/articles/article-child-cold.svg',
                'meta_title'      => 'Recurrent Colds in Children — Homoeopathy for Kids | Port Blair',
                'meta_description'=> 'Why children catch six colds a year, when antibiotics are not the answer, '
                    . 'and how homoeopathic prescribing plus simple hygiene can reduce repeat infections.',
                'excerpt'         => 'A child who catches a cold every month is usually not weak — the pattern '
                    . 'is common, and there is a better answer than a fresh antibiotic course each time.',
                'body'            => '<p>Almost every parent in Port Blair eventually asks the same question: '
                    . 'my child has been ill again, and this is the fourth time this year. It is a genuinely '
                    . 'tiring cycle, and it deserves a proper answer rather than another prescription.</p>'
                    . '<h3>How many colds are actually normal?</h3>'
                    . '<p>A child in their first few years of school may catch six to eight upper respiratory '
                    . 'infections in a year and this is still within the normal range. Children meet other '
                    . 'children for the first time in large groups, and each exposure is a small immune '
                    . 'lesson. What matters is not the number of colds but whether the child is well between '
                    . 'them — feeding, sleeping, growing and playing normally.</p>'
                    . '<h3>When the pattern is not normal</h3>'
                    . '<p>Consider a proper assessment when any of these are true:</p>'
                    . '<ul><li>Colds run continuously for more than ten days or keep coming straight back '
                    . 'within days of resolving.</li><li>There is a barking cough, wheeze or breathlessness '
                    . 'at night.</li><li>The child is not gaining weight or has lost it.</li><li>There is a '
                    . 'persistent fever, or a rash that does not fade when pressed with a glass.</li>'
                    . '<li>Ear infections occur repeatedly on the same side.</li></ul>'
                    . '<p>Any of these need a doctor, and a chest examination plus a blood count or chest '
                    . 'X-ray may be more useful than another course of the same medicine.</p>'
                    . '<h3>What homoeopathy is used for</h3>'
                    . '<p>Classical homoeopathic prescribing matches a remedy to your child’s own '
                    . 'symptom picture — how the cold starts, the nature of the discharge, whether they '
                    . 'are thirsty or utterly uninterested in water, whether they want to be carried or '
                    . 'left alone, how sleep and appetite change. Where that pattern is consistent, treatment '
                    . 'aims at the child’s tendency rather than the individual week.</p>'
                    . '<p>Used this way, homoeopathy has a reasonable place alongside conventional care. It is '
                    . 'not a substitute for antibiotics when a bacterial infection is genuinely present, and '
                    . 'it does not replace vaccination.</p>'
                    . '<h3>The unglamorous things that work</h3>'
                    . '<ol><li>Handwashing after school and before meals, and a basin placed where it gets '
                    . 'used rather than being asked for.</li><li>Sleep. A child short of sleep catches '
                    . 'everything in the classroom.</li><li>Not wrapping a child warmly indoors — '
                    . 'over-bundling makes the common cold more frequent, not less.</li><li>Fluids and '
                    . 'light food during a cold; appetite usually returns within a few days and forcing '
                    . 'food is counterproductive.</li><li>Honey for cough in children over one year, in '
                    . 'small amounts.</li></ol>'
                    . '<h3>When to go to hospital, not the clinic</h3>'
                    . '<p>Breathing that is fast or laboured, ribs sucking in with each breath, blue lips, '
                    . 'drowsiness that is hard to wake from, a convulsion, or refusal to drink for more than '
                    . 'eight hours are all reasons to go to the emergency department immediately. Call 112.</p>'
                    . '<p>If your child is under one year old and has a fever, contact a doctor the same day '
                    . 'rather than waiting.</p>',
            ],
            [
                'slug'            => 'acidity-and-gastritis-what-you-can-do',
                'title'           => 'Acidity and Gastritis: A Practical Plan for Port Blair Households',
                'category'        => 'Digestive health',
                'read_minutes'    => 5,
                'image'           => '/assets/images/articles/article-acidity.svg',
                'meta_title'      => 'Acidity and Gastritis — Causes, Relief and Homoeopathy',
                'meta_description'=> 'Why acidity is so common in the Andamans, the everyday habits that make '
                    . 'it worse, how homoeopathy approaches gastritis, and the warning signs that need a scan.',
                'excerpt'         => 'Burning after food, a sour taste in the morning, bloating by evening. '
                    . 'Most of it is fixable — but some patterns need a gastrologist, not a remedy.',
                'body'            => '<p>Acidity is one of the most common complaints at our clinic, and one '
                    . 'of the most frustrating for patients, because most people treat it as a stomach '
                    . 'problem when it is usually a lifestyle and sleep problem.</p>'
                    . '<h3>Why it is so common here</h3>'
                    . '<p>Three local factors combine badly. Humid tropical air slows gastric emptying. Spicy '
                    . 'coconut-and-chilli food is a daily habit, not an occasional indulgence. And irregular '
                    . 'timing — a long working day, then a heavy late meal, then late sleep — leaves the '
                    . 'stomach empty for long stretches and then asks it to digest everything at once.</p>'
                    . '<p>Coffee on an empty stomach, tobacco, NSAIDs such as ibuprofen taken for any reason, '
                    . 'and drinking large amounts of water with meals all make it worse.</p>'
                    . '<h3>Immediate changes worth making this week</h3>'
                    . '<ul><li>Finish dinner at least two hours before sleeping.</li><li>Eat smaller meals '
                    . 'more often rather than two large ones.</li><li>Stop coffee before 9 AM, or take it '
                    . 'with food rather than instead of it.</li><li>Raise the head of the bed six to eight '
                    . 'inches if night-time reflux is your main problem.</li><li>Reduce chillies and oil in '
                    . 'the evening meal specifically, not across the whole day.</li><li>Stop any NSAID you '
                    . 'do not need — ask your doctor first.</li></ul>'
                    . '<h3>How homoeopathy approaches gastritis</h3>'
                    . '<p>The prescription is matched to the whole digestive picture: the exact site and '
                    . 'timing of the burning, whether food or an empty stomach aggravates it, your thirst, '
                    . 'bowel pattern, what relieves you, and your general temperature and sleep preference. '
                    . 'Constitutional treatment over a few months tends to reduce both the frequency and the '
                    . 'intensity of episodes; during an acute flare the same remedy often brings quick '
                    . 'relief.</p>'
                    . '<h3>When you need a gastrologist, not us</h3>'
                    . '<p>Please get these assessed properly, and go without delay if they apply:</p>'
                    . '<ul><li>Difficulty or pain swallowing food, or food feeling stuck.</li>'
                    . '<li>Vomiting blood, or black tarry stools.</li><li>Unexplained weight loss, or '
                    . 'ongoing vomiting.</li><li>Jaundice, or severe pain in the upper right abdomen.</li>'
                    . '<li>Anaemia found on a blood test, or a family history of gastric cancer.</li>'
                    . '<li>Symptoms that began after the age of 50.</li></ul>'
                    . '<p>An endoscopy is uncomfortable but it answers questions that no remedy can. We would '
                    . 'rather send you for one than treat a possible ulcer blind for six months.</p>',
            ],
            [
                'slug'            => 'eczema-and-skin-allergy-long-term-plan',
                'title'           => 'Eczema and Skin Allergy: A Long-Term Plan That Actually Works',
                'category'        => 'Skin and allergy',
                'read_minutes'    => 7,
                'image'           => '/assets/images/articles/article-eczema.svg',
                'meta_title'      => 'Eczema and Skin Allergy — Long-Term Treatment Plan',
                'meta_description'=> 'Eczema flares because of triggers and broken skin barrier, not because '
                    . 'of poor hygiene. A staged plan covering barrier repair, trigger control and homoeopathic '
                    . 'prescribing.',
                'excerpt'         => 'Eczema is not a hygiene problem and it is not something you simply live '
                    . 'with. Here is the staged plan we give patients, and what each stage is actually for.',
                'body'            => '<p>Almost every eczema patient we see has already tried something. The '
                    . 'problem is that most of those attempts target the flare instead of the two things '
                    . 'that cause it: a damaged skin barrier and a trigger the body is reacting to.</p>'
                    . '<h3>Stage 1 — Repair the barrier (first four to six weeks)</h3>'
                    . '<p>Until the barrier is intact, creams barely absorb and everything irritates. This '
                    . 'stage is boring and it is the foundation:</p>'
                    . '<ul><li>Emollient applied at least twice daily, within a few minutes of a short, '
                    . 'lukewarm bath. Quantity matters — an adult often needs 250 g a week.</li>'
                    . '<li>Short baths, no long soaking, no scrubbing, no hot water.</li>'
                    . '<li>Soap-free or very mild cleanser.</li><li>Loose cotton clothing; avoid wool next to '
                    . 'skin.</li><li>Nail the child short if scratching is a problem — it does more good '
                    . 'than any cream.</li></ul>'
                    . '<h3>Stage 2 — Find and remove triggers</h3>'
                    . '<p>The commonest triggers in this region are dust and house-dust mite, coconut and '
                    . 'fragrance in soaps and detergents, sweat, wool and synthetic fabric, and certain '
                    . 'foods in some children. Patch testing by a dermatologist, where available, is more '
                    . 'reliable than guessing. Keep a simple diary of flare versus activity, clothing, food '
                    . 'and weather.</p>'
                    . '<h3>Stage 3 — Homoeopathic prescribing</h3>'
                    . '<p>Once the barrier is repairing, a constitutional remedy matched to the whole case '
                    . 'is given and reviewed on a fixed schedule. Expect this to take months, not weeks. The '
                    . 'goal is longer flare-free intervals and less dependence on topical steroids, not a '
                    . 'miracle. Do not reduce prescribed steroid creams on your own — reduce them with '
                    . 'the dermatologist who prescribed them.</p>'
                    . '<h3>Stage 4 — Know the flare plan in advance</h3>'
                    . '<p>Write down what you do when a flare starts, so that at 2 AM you are not deciding '
                    . 'anything new. Most flares need escalation of the same routine, not a different '
                    . 'medicine.</p>'
                    . '<h3>See a dermatologist rather than waiting</h3>'
                    . '<p>A rapidly spreading red, hot, painful rash with fever; weeping or crusted skin '
                    . 'that spreads fast; a blistering rash; a rash that does not blanch when pressed with a '
                    . 'glass; or any mole that is changing in size, shape or colour. Eczema can also become '
                    . 'infected, and that needs antibiotics, not a remedy.</p>',
            ],
            [
                'slug'            => 'homoeopathy-and-pregnancy-safety',
                'title'           => 'Homoeopathy and Pregnancy: What Is Safe and What Is Not',
                'category'        => 'Women’s health',
                'read_minutes'    => 6,
                'image'           => '/assets/images/articles/article-pregnancy.svg',
                'meta_title'      => 'Homoeopathy in Pregnancy — Safety, Timing and Limits',
                'meta_description'=> 'What homoeopathic prescribing can and cannot do during pregnancy, why '
                    . 'the first trimester needs particular caution, and which symptoms need a hospital.',
                'excerpt'         => 'Pregnancy is the situation where patients most often need an honest '
                    . 'answer rather than a confident one. Here is exactly how we handle it.',
                'body'            => '<p>Pregnancy is not a condition to be treated casually, and we are '
                    . 'conservative by design. These are the rules we follow, and we ask every patient to '
                    . 'agree to them before we prescribe anything.</p>'
                    . '<h3>Our rules</h3>'
                    . '<ol><li>No homoeopathic prescribing in the first trimester, except where a doctor '
                    . 'judges the risk of not treating to be higher than the risk of treating.</li>'
                    . '<li>Pregnancy must be confirmed by a qualified obstetrician, with your antenatal '
                    . 'records available at every visit.</li><li>Every allopathic medicine you have been '
                    . 'given continues exactly as prescribed. We never ask you to stop anything.</li>'
                    . '<li>Only lower potencies are used, and the smallest effective number of repetitions.</li>'
                    . '<li>You are reviewed more often than a non-pregnant patient — typically every two '
                    . 'weeks — because the picture changes quickly.</li></ol>'
                    . '<h3>What patients usually come for</h3>'
                    . '<p>Nausea and vomiting of pregnancy, heartburn and reflux, constipation, insomnia, '
                    . 'anxiety about the pregnancy, back and pelvic discomfort, and preparation for labour. '
                    . 'These are common, distressing and often manageable without any medicine at all — '
                    . 'small frequent meals, ginger, avoiding lying down straight after eating, and a '
                    . 'consistent sleep routine help more than most people expect.</p>'
                    . '<h3>What we will not attempt</h3>'
                    . '<p>We do not attempt to change the course of a pregnancy, induce labour, or treat a '
                    . 'medical condition of pregnancy. Pre-eclampsia, gestational diabetes, placenta '
                    . 'abruption, bleeding, severe headache with visual disturbance, or reduced fetal '
                    . 'movement belong in the hospital, immediately.</p>'
                    . '<h3>After delivery</h3>'
                    . '<p>The six weeks after delivery change a woman’s symptom picture completely, and '
                    . 'this period is frequently overlooked. If you are breastfeeding, tell us — the '
                    . 'prescription has to account for that, and no medicine is given on assumption. If you '
                    . 'are feeling low, tearful, or unable to sleep or eat after the birth of your baby, say '
                    . 'so explicitly. Postnatal depression is common, treatable, and not something to wait out '
                    . 'alone.</p>',
            ],
            [
                'slug'            => 'homoeopathy-for-back-and-knee-pain',
                'title'           => 'Back and Knee Pain: What You Can Fix and What Needs a Scan',
                'category'        => 'Musculoskeletal',
                'read_minutes'    => 5,
                'image'           => '/assets/images/articles/article-back-pain.svg',
                'meta_title'      => 'Back and Knee Pain — Self Care, Homoeopathy and Red Flags',
                'meta_description'=> 'Most mechanical back and knee pain responds to loading, movement and a '
                    . 'constitutional remedy. This is how to tell it apart from pain that needs imaging or surgery.',
                'excerpt'         => 'Mechanical back pain is common, serious-sounding and usually manageable. '
                    . 'The trick is knowing the difference between it and pain that needs a scan.',
                'body'            => '<p>Roughly four in five episodes of lower back pain are mechanical: '
                    . 'nothing structural is damaged, the tissues are simply irritated. This is good news, '
                    . 'because mechanical pain responds to movement and time.</p>'
                    . '<h3>The two rules that help most</h3>'
                    . '<ul><li><strong>Keep moving.</strong> The strongest evidence in back pain is that '
                    . 'bed rest makes it worse. Gentle walking from day one, and increasing distance daily, '
                    . 'beats a week on the sofa.</li><li><strong>Load it sensibly.</strong> Walking, swimming '
                    . 'and cycling are safer than prolonged sitting. Lift close to your body, bend the knees, '
                    . 'and stop a lift that makes you hold your breath.</li></ul>'
                    . '<p>Physiotherapy-guided exercises, done consistently, are the single most effective '
                    . 'intervention for chronic low back pain. A home exercise sheet you actually complete '
                    . 'beats a course you do not finish.</p>'
                    . '<h3>What homoeopathy adds</h3>'
                    . '<p>A constitutional remedy matched to the whole muscular and general picture can reduce '
                    . 'the frequency and severity of episodes for people who are recurrent sufferers, and '
                    . 'can be used safely alongside physiotherapy. It is not a substitute for a structured '
                    . 'exercise programme, and we will say so.</p>'
                    . '<h3>Red flags — get assessed today</h3>'
                    . '<p>Seek an orthopaedic or surgical assessment, not a homoeopathic appointment, if you '
                    . 'have:</p>'
                    . '<ul><li>Numbness around the groin, inner thighs or buttocks, or difficulty passing or '
                    . 'controlling urine. This may be cauda equina syndrome and it is a surgical emergency.</li>'
                    . '<li>Back pain with fever, or after a recent infection.</li><li>Severe pain after a '
                    . 'fall or a road traffic accident.</li><li>Pain at night that wakes you and does not ease '
                    . 'with rest, or unexplained weight loss.</li><li>Numbness, weakness or wasting in a leg, '
                    . 'particularly in a smoker.</li><li>Back pain in a person with a history of cancer, or '
                    . 'long-term steroid use.</li></ul>'
                    . '<p>For the knee: locking, giving way, a hot swollen joint, or inability to bear weight '
                    . 'after injury all need assessment. A knee that swells within hours of a twist may have '
                    . 'a meniscal or ligament injury that is repairable if seen early.</p>',
            ],
            [
                'slug'            => 'understanding-your-homoeopathy-prescription',
                'title'           => 'Understanding Your Homoeopathy Prescription',
                'category'        => 'Treatment basics',
                'read_minutes'    => 4,
                'image'           => '/assets/images/articles/article-prescription.svg',
                'meta_title'      => 'How to Read and Follow Your Homoeopathy Prescription',
                'meta_description'=> 'Potency, dose, timing, repetitions and what to do if you miss a dose — '
                    . 'a plain-language guide to taking homoeopathic medicine correctly.',
                'excerpt'         => 'The most common reason a homoeopathic prescription underperforms is '
                    . 'not the remedy. It is how it was taken. Here is the short version.',
                'body'            => '<p>Patients are often nervous about taking a small white pill, and '
                    . 'frequently receive contradictory instructions from well-meaning relatives. This is what '
                    . 'your written prescription means.</p>'
                    . '<h3>Reading the label</h3>'
                    . '<p>Your label carries four things: the <strong>remedy name</strong> '
                    . '(for example <em>Sulphur</em>), the <strong>potency</strong> '
                    . '(6C, 30C, 200C, 1M and so on), the number of <strong>pills per dose</strong>, and how '
                    . 'many <strong>doses per day</strong>. Lower numbers mean more dilution and a gentler '
                    . 'action. If two bottles are given, they are not taken together — the doctor will tell '
                    . 'you the gap.</p>'
                    . '<h3>How to take it</h3>'
                    . '<ul><li>Let the pill dissolve on the tongue. Do not swallow it whole with water.</li>'
                    . '<li>Take it roughly half an hour before or after food, not during a heavy meal.</li>'
                    . '<li>Keep the bottle closed and away from camphor, mothballs, strong perfume, menthol '
                    . 'and varnishes. Smells can spoil a remedy through the cap.</li><li>Do not touch the '
                    . 'pills with your fingers; tip them into the cap.</li><li>Do not crush them unless the '
                    . 'doctor has said it is fine — an infant or a bedridden parent is the usual exception, '
                    . 'and the doctor will show you how.</li><li>Store in a cool, dry, dark place. Do not '
                    . 'refrigerate unless instructed.</li></ul>'
                    . '<h3>What improvement looks like</h3>'
                    . '<p>For a chronic condition, look for change in the order the doctor explained — '
                    . 'usually sleep and appetite first, then the intensity of symptoms, then frequency. Do '
                    . 'not expect a remedy to work through every past illness you have ever had. A short '
                    . 'review before the first prescription is finished is usually more informative than '
                    . 'running a bottle to empty.</p>'
                    . '<h3>If you miss a dose</h3>'
                    . '<p>Take the next dose as usual. Do not double up. A missed dose is not a clinical '
                    . 'event.</p>'
                    . '<h3>If symptoms get worse</h3>'
                    . '<p>Contact the clinic. A genuine aggravation in a well-selected prescription is '
                    . 'usually brief and mild, and the doctor may deliberately continue, reduce or stop the '
                    . 'remedy. What matters is that you report it, rather than quietly stopping treatment or '
                    . 'adding something from a chemist on your own.</p>',
            ],
        ];
    }

    /** @return array<int,array{slug:string,title:string}> */
    public static function index(): array
    {
        return array_map(
            static fn (array $a): array => ['slug' => $a['slug'], 'title' => $a['title']],
            self::all()
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        foreach (self::all() as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }
}
