<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Services\SubmissionStore;
use Throwable;

/**
 * Website contact-form messages, with the same database/file fallback
 * behaviour as appointments.
 */
final class Message
{
    /**
     * @param array<string,mixed> $data
     * @return array{id:int|null,stored:'db'|'file',error:string}
     */
    public static function create(array $data): array
    {
        $row = [
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'],
            'subject'    => $data['subject'] ?? 'General enquiry',
            'message'    => $data['message'],
            'status'     => 'unread',
            'spam_score' => (int) ($data['spam_score'] ?? 0),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $data['updated_at'] ?? $data['created_at'] ?? date('Y-m-d H:i:s'),
        ];

        try {
            $id = Database::instance()->insert('messages', $row);

            return ['id' => $id, 'stored' => 'db', 'error' => ''];
        } catch (Throwable $e) {
            SubmissionStore::append('messages', $row + ['_error' => $e->getMessage()]);

            return ['id' => null, 'stored' => 'file', 'error' => $e->getMessage()];
        }
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,array<string,mixed>>
     */
    public static function list(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        [$where, $params] = self::buildFilters($filters);

        try {
            $sql = 'SELECT * FROM messages' . ($where === '' ? '' : ' WHERE ' . $where)
                . ' ORDER BY created_at DESC LIMIT :lim OFFSET :off';

            return Database::instance()->select($sql, array_merge($params, [
                'lim' => max(1, min(500, $limit)),
                'off' => max(0, $offset),
            ]));
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        try {
            return Database::instance()->first('SELECT * FROM messages WHERE id = :id', ['id' => $id]);
        } catch (Throwable) {
            return null;
        }
    }

    public static function markRead(int $id): bool
    {
        try {
            Database::instance()->update('messages', ['status' => 'read', 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        try {
            return Database::instance()->delete('messages', 'id = :id', ['id' => $id]) > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /** @return array{unread:int,total:int} */
    public static function counts(): array
    {
        try {
            $db    = Database::instance();
            $total = (int) $db->value('SELECT COUNT(*) FROM messages');
            $unread = (int) $db->value("SELECT COUNT(*) FROM messages WHERE status = 'unread'");

            return ['unread' => $unread, 'total' => $total];
        } catch (Throwable) {
            return ['unread' => 0, 'total' => 0];
        }
    }

    /**
     * @param array<string,mixed> $filters
     * @return array{0:string,1:array<string,mixed>}
     */
    private static function buildFilters(array $filters): array
    {
        $clauses = [];
        $params  = [];

        if (!empty($filters['status']) && in_array($filters['status'], ['unread', 'read', 'archived'], true)) {
            $clauses[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $clauses[] = '(name LIKE :q OR email LIKE :q OR phone LIKE :q OR subject LIKE :q OR message LIKE :q)';
            $params['q'] = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $filters['q']) . '%';
        }

        return [implode(' AND ', $clauses), $params];
    }
}
