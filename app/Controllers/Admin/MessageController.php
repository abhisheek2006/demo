<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Content\Clinic;
use App\Core\Response;
use App\Core\Session;
use App\Models\Message;

/**
 * Contact-message management.
 */
final class MessageController extends AdminController
{
    private const PER_PAGE = 25;

    public function index(): Response
    {
        $filters = [
            'status' => $this->request->string('status'),
            'q'      => mb_substr($this->request->string('q'), 0, 100),
        ];

        $page = max(1, (int) $this->request->string('page', '1'));
        $page = $page ?: 1;

        $rows = Message::list($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        return $this->view('admin/messages', [
            'page'     => 'messages',
            'clinic'   => Clinic::info(),
            'rows'     => $rows,
            'filters'  => $filters,
            'counts'   => Message::counts(),
            'current'  => $page,
        ]);
    }

    public function read(string $id = ''): Response
    {
        $messageId = (int) $id;
        $message   = Message::find($messageId);

        if ($message === null) {
            Session::flashError('That message could not be found.');

            return $this->redirect('/admin/messages');
        }

        if (($message['status'] ?? '') === 'unread') {
            Message::markRead($messageId);
        }

        return $this->view('admin/message', [
            'page'    => 'messages',
            'clinic'  => Clinic::info(),
            'message' => $message,
        ]);
    }

    public function delete(string $id = ''): Response
    {
        $this->assertCsrf();

        $messageId = (int) $id;
        if ($messageId < 1) {
            Session::flashError('Invalid message reference.');

            return $this->redirect('/admin/messages');
        }

        $confirm = $this->request->string('confirm');
        if ($confirm !== (string) $messageId && $confirm !== 'yes') {
            Session::flashError('Deletion cancelled — confirmation did not match.');

            return $this->redirect('/admin/messages');
        }

        $deleted = Message::delete($messageId);

        Session::flash($deleted ? 'success' : 'error', $deleted
            ? 'Message deleted.'
            : 'That message could not be deleted. It may already be gone.');

        return $this->redirect('/admin/messages');
    }
}
