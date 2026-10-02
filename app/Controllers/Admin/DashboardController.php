<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Content\Clinic;
use App\Core\Config;
use App\Core\Database;
use App\Core\Response;
use App\Models\Appointment;
use App\Models\Message;
use App\Services\SubmissionStore;
use Throwable;

/**
 * Admin dashboard.
 */
final class DashboardController extends AdminController
{
    public function index(): Response
    {
        $dbOk = false;
        try {
            $dbOk = Database::instance()->isAvailable();
        } catch (Throwable) {
            $dbOk = false;
        }

        $fallback = $dbOk
            ? ['appointments' => 0, 'messages' => 0]
            : [
                'appointments' => SubmissionStore::countRecords('appointments'),
                'messages'     => SubmissionStore::countRecords('messages'),
            ];

        $recent = $dbOk ? Appointment::list([], 6) : [];

        $mailer  = new \App\Services\Mailer();
        $envOk   = trim((string) \App\Core\Env::string('APP_KEY', '')) !== '';

        $inventory = SubmissionStore::inventory();

        return $this->view('admin/dashboard', [
            'page'          => 'dashboard',
            'clinic'        => Clinic::info(),
            'dbOk'          => $dbOk,
            'dbError'       => $dbOk ? '' : (string) (Database::instance()->lastError() ?? 'Database unavailable'),
            'statusCounts'  => $dbOk ? Appointment::statusCounts() : array_fill_keys(Appointment::STATUSES, 0),
            'messageCounts' => $dbOk ? Message::counts() : $fallback,
            'recent'        => $recent,
            'fallback'      => $fallback,
            'inventory'     => $inventory,
            'mailReady'     => $mailer->isConfigured(),
            'mailReason'    => $mailer->isConfigured() ? '' : $mailer->reason(),
            'appKeySet'     => $envOk,
            'debug'         => Config::debug(),
        ]);
    }
}
