<?php
declare(strict_types=1);

namespace App\Models;

use App\Content\FaqData;
use App\Core\Database;
use Throwable;

/**
 * FAQ rows. Reads fall back to the bundled content when the database is not
 * available, so the FAQ page and its JSON-LD always work.
 */
final class Faq
{
    /** Questions intentionally retired from the public FAQ. */
    private const HIDDEN_PUBLIC_QUESTIONS = [
        'How do I book a consultation?',
        'Do I need a confirmed appointment, or can I walk in?',
        'How long does homoeopathic treatment take to show results?',
        'How many medicines will I be given?',
        'Do you offer home visits?',
    ];

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function published(?string $category = null): array
    {
        try {
            $db    = Database::instance();
            $params = [];
            $where  = 'is_published = 1';

            if ($category !== null && $category !== '') {
                $where .= ' AND category = :category';
                $params['category'] = $category;
            }

            $rows = $db->select(
                'SELECT id, question, answer, category, sort_order, is_published, created_at, updated_at
                 FROM faqs WHERE ' . $where . ' ORDER BY sort_order ASC, id ASC',
                $params
            );

            if ($rows !== []) {
                $rows = array_values(array_filter($rows, static function (array $row): bool {
                    $question = trim((string) ($row['question'] ?? ''));

                    return strcasecmp($question, 'Do you accept insurance?') !== 0
                        && !in_array($question, self::HIDDEN_PUBLIC_QUESTIONS, true);
                }));
                $canonical = [];
                foreach (FaqData::all() as $default) {
                    $canonical[mb_strtolower(trim($default['question']))] = $default;
                }
                $aliases = [
                    'is homoeopathy safe without side effects?' => 'is homoeopathy safe?',
                    'can i stop my allopathic medicines once i feel better?' => 'can i take homoeopathic medicines alongside allopathic medication, or should i stop my current medicines?',
                ];
                foreach ($rows as &$row) {
                    $key = mb_strtolower(trim((string) ($row['question'] ?? '')));
                    $key = $aliases[$key] ?? $key;
                    if (isset($canonical[$key])) {
                        $row['question'] = $canonical[$key]['question'];
                        $row['answer'] = $canonical[$key]['answer'];
                    }
                }
                unset($row);

                return $rows;
            }
        } catch (Throwable) {
            // Fall through to bundled content.
        }

        $rows = FaqData::all();
        if ($category !== null && $category !== '') {
            $rows = array_values(array_filter($rows, static fn (array $r): bool => $r['category'] === $category));
        }

        return $rows;
    }

    /**
     * Grouped by category for the accordion UI.
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::published() as $row) {
            $grouped[(string) ($row['category'] ?? 'General')][] = $row;
        }

        if ($grouped === []) {
            $grouped = FaqData::groups();
        }

        return $grouped;
    }

    /**
     * @return array<int,array{question:string,answer:string}>
     */
    public static function forSchema(): array
    {
        $out = [];

        foreach (self::published() as $row) {
            $out[] = [
                'question' => (string) $row['question'],
                'answer'   => (string) $row['answer'],
            ];
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(): array
    {
        try {
            return Database::instance()->select('SELECT * FROM faqs ORDER BY category ASC, sort_order ASC, id ASC');
        } catch (Throwable) {
            return FaqData::all();
        }
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        try {
            return Database::instance()->first('SELECT * FROM faqs WHERE id = :id', ['id' => $id]);
        } catch (Throwable) {
            return null;
        }
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        return Database::instance()->insert('faqs', [
            'question'     => $data['question'],
            'answer'       => $data['answer'],
            'category'     => $data['category'] ?? 'General',
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
            'is_published' => (int) ($data['is_published'] ?? 1),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): int
    {
        return Database::instance()->update('faqs', [
            'question'     => $data['question'],
            'answer'       => $data['answer'],
            'category'     => $data['category'] ?? 'General',
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
            'is_published' => (int) ($data['is_published'] ?? 1),
            'updated_at'   => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): int
    {
        return Database::instance()->delete('faqs', 'id = :id', ['id' => $id]);
    }

    public static function countAll(): int
    {
        try {
            return (int) Database::instance()->value('SELECT COUNT(*) FROM faqs');
        } catch (Throwable) {
            return count(FaqData::all());
        }
    }

    /** @return string[] */
    public static function categories(): array
    {
        $categories = [];

        foreach (self::all() as $row) {
            $category = trim((string) ($row['category'] ?? ''));
            if ($category !== '' && !in_array($category, $categories, true)) {
                $categories[] = $category;
            }
        }

        return $categories !== [] ? $categories : FaqData::categories();
    }
}
