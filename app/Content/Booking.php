<?php
declare(strict_types=1);

namespace App\Content;

/**
 * Booking form configuration: consultation types, time slots, reason lists and
 * the copy shown around the appointment forms.
 */
final class Booking
{
    public const TYPE_CONSULTATION = 'consultation';
    public const TYPE_FOLLOW_UP    = 'follow_up';

    /** @return array<string,string> */
    public static function types(): array
    {
        return [
            self::TYPE_CONSULTATION => 'New consultation',
            self::TYPE_FOLLOW_UP    => 'Follow-up review',
        ];
    }

    /**
     * Appointment slots in 12-hour display form. Sunday slots are excluded
     * because the clinic is closed apart from emergency calls.
     *
     * @return array<int,string>
     */
    public static function timeSlots(): array
    {
        return [
            '10:00', '10:30', '11:00', '11:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
        ];
    }

    /**
     * Reasons offered as quick-select chips. The `reason` field also accepts
     * free text, so this is a convenience, not a restriction.
     *
     * @return array<int,string>
     */
    public static function commonReasons(): array
    {
        return [
            'Fever, cough or cold',
            'Acidity or stomach trouble',
            'Skin allergy or eczema',
            'Back or joint pain',
            'Women’s health concern',
            'Anxiety or sleep problem',
            'Childhood complaint',
            'Weight or lifestyle concern',
        ];
    }

    /**
     * How far ahead a patient may book.
     */
    public static function maxAdvanceDays(): int
    {
        return 60;
    }

    /** @return array<int,string> */
    public static function ageRange(): array
    {
        return [
            'Infant (0–1 year)',
            'Child (1–12 years)',
            'Teenager (13–17 years)',
            'Adult (18–59 years)',
            'Senior (60 years and above)',
        ];
    }

    /**
     * What the patient should read before submitting.
     *
     * @return array<int,string>
     */
    public static function preparation(): array
    {
        return [
            'Bring every medicine you are currently taking, including supplements, in their original '
            . 'packaging or with the doses written down.',
            'Bring any investigation reports — blood tests, ultrasound, X-ray, prescriptions from other '
            . 'doctors — and your antenatal records if you are pregnant.',
            'Note roughly when your symptoms started, how they have changed, and what makes them better or '
            . 'worse.',
            'Do not stop any existing medication before your visit.',
            'Arrive ten minutes early so the history can be taken without rushing.',
        ];
    }

    /**
     * Copy shown beneath the confirmation message.
     *
     * @return array<int,string>
     */
    public static function afterBooking(): array
    {
        return [
            'We will call you to confirm the exact time, usually the same working day.',
            'If you cannot reach us, message the same number on WhatsApp and we will respond there.',
            'If you are running a fever on the day, inform us so we can decide whether to see you or '
            . 'advise a hospital visit first.',
            'If your condition is an emergency, do not wait for a confirmation — call 112.',
        ];
    }

    /**
     * Reference code prefix per booking type.
     */
    public static function referencePrefix(string $type): string
    {
        return $type === self::TYPE_FOLLOW_UP ? 'SHF' : 'SHC';
    }
}
