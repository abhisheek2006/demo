<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Content\Clinic;
use App\Core\Response;
use App\Core\Session;
use App\Models\Appointment;
use App\Services\Notifications;

/**
 * Appointment management: list, filter, change status, delete.
 */
final class AppointmentController extends AdminController
{
    private const PER_PAGE = 25;

    public function index(): Response
    {
        $filters = [
            'status' => $this->request->string('status'),
            'type'   => $this->request->string('type'),
            'q'      => mb_substr($this->request->string('q'), 0, 100),
            'from'   => $this->request->string('from'),
            'to'     => $this->request->string('to'),
        ];

        $page = max(1, (int) $this->request->string('page', '1'));
        $page = $page ?: 1;

        $rows  = Appointment::list($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
        $total = Appointment::count($filters);

        return $this->view('admin/appointments', [
            'page'        => 'appointments',
            'clinic'      => Clinic::info(),
            'rows'        => $rows,
            'filters'     => $filters,
            'counts'      => Appointment::statusCounts(),
            'total'       => $total,
            'perPage'     => self::PER_PAGE,
            'current'     => $page,
            'lastPage'    => max(1, (int) ceil($total / self::PER_PAGE)),
            'statuses'    => Appointment::STATUSES,
            'statusLabels'=> Appointment::STATUS_LABELS,
        ]);
    }

    public function updateStatus(string $id = ''): Response
    {
        $this->assertCsrf();

        $appointmentId = (int) $id;
        $status        = $this->request->string('status');
        $note          = $this->request->string('admin_note');

        if ($appointmentId < 1) {
            Session::flashError('Invalid appointment reference.');

            return $this->redirect('/admin/appointments');
        }

        if (!in_array($status, Appointment::STATUSES, true)) {
            Session::flashError('Unknown status value.');

            return $this->redirect('/admin/appointments');
        }

        $appointment = Appointment::find($appointmentId);
        if ($appointment === null) {
            Session::flashError('That appointment could not be found.');

            return $this->redirect('/admin/appointments');
        }

        Appointment::updateStatus($appointmentId, $status, $note !== '' ? $note : null);

        // Optional notification to the patient.
        if ($status === 'confirmed' && $this->request->raw('notify') === '1') {
            $result = Notifications::appointmentRequested([
                'reference_code' => (string) $appointment['reference_code'],
                'type'           => (string) $appointment['type'],
                'full_name'      => (string) $appointment['full_name'],
                'phone'          => (string) $appointment['phone'],
                'email'          => (string) $appointment['email'],
                'age'            => (string) $appointment['age'],
                'preferred_date' => (string) $appointment['preferred_date'],
                'preferred_time' => (string) $appointment['preferred_time'],
                'reason'         => (string) $appointment['reason'],
                'message'        => 'Confirmed by the clinic. Your appointment is booked for '
                    . format_date((string) $appointment['preferred_date'])
                    . ' at ' . minutes_to_label((string) $appointment['preferred_time']) . '.',
                'consent'        => 1,
                'source'         => 'admin-confirmation',
            ]);

            Session::flash($result['sent'] ? 'success' : 'error', $result['sent']
                ? 'Appointment confirmed and the patient has been emailed.'
                : 'Appointment confirmed, but the confirmation email could not be sent (' . $result['reason'] . ').');
        } else {
            Session::flashSuccess('Appointment status updated to ' . (Appointment::STATUS_LABELS[$status] ?? $status) . '.');
        }

        return $this->redirect($this->backLink());
    }

    public function delete(string $id = ''): Response
    {
        $this->assertCsrf();

        $appointmentId = (int) $id;
        if ($appointmentId < 1) {
            Session::flashError('Invalid appointment reference.');

            return $this->redirect('/admin/appointments');
        }

        $confirm = $this->request->string('confirm');
        if ($confirm !== $appointmentId . '' && $confirm !== 'yes') {
            Session::flashError('Deletion cancelled — confirmation did not match.');

            return $this->redirect($this->backLink());
        }

        $deleted = Appointment::delete($appointmentId);

        Session::flash($deleted ? 'success' : 'error', $deleted
            ? 'Appointment deleted.'
            : 'That appointment could not be deleted. It may already be gone.');

        return $this->redirect('/admin/appointments');
    }

    private function backLink(): string
    {
        $referer = $this->request->header('Referer');
        if ($referer !== null && str_contains($referer, (string) $this->request->server('HTTP_HOST', ''))
            && str_contains($referer, '/admin')) {
            return $referer;
        }

        return '/admin/appointments';
    }
}
