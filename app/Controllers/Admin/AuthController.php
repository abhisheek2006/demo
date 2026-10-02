<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Content\Clinic;
use App\Core\Controller;
use App\Core\Exceptions\HttpException;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Services\RateLimiter;

/**
 * Administrator sign-in and sign-out.
 */
final class AuthController extends Controller
{
    protected string $layout = 'layouts/admin-auth';

    public function showLogin(): Response
    {
        if (User::check()) {
            return $this->redirect('/admin');
        }

        // The flash bag was already drained into the shared view data during
        // bootstrap, so read it from there rather than consuming it twice.
        $shared   = \App\Core\View::shared();
        $oldData  = is_array($shared['oldInput'] ?? null) ? $shared['oldInput'] : [];
        $errData  = is_array($shared['flashedErrors'] ?? null) ? $shared['flashedErrors'] : [];

        return $this->view('admin/login', [
            'page'     => 'login',
            'clinic'   => Clinic::info(),
            'old'      => $oldData,
            'errors'   => $errData,
            'locked'   => User::lockedOut(),
            'attempts' => User::attempts(),
        ]);
    }

    public function login(): Response
    {
        if (!$this->request->isPost()) {
            return $this->redirect('/admin/login');
        }

        $this->assertCsrf();

        $email    = $this->request->string('email');
        $password = (string) $this->request->raw('password', '');

        if (User::lockedOut()) {
            Session::flashError('Too many failed sign-in attempts. Please wait 15 minutes and try again.');

            return $this->redirect('/admin/login');
        }

        if (!RateLimiter::attempt('login', $this->request->ip())) {
            Session::flashError('Too many sign-in attempts from this connection. Please wait and try again.');

            return $this->redirect('/admin/login');
        }

        if ($password === '' || strlen($password) > 200) {
            Session::flashValidation(
                ['password' => 'Enter your password.'],
                ['email' => $email]
            );

            return $this->redirect('/admin/login');
        }

        $result = User::attempt($email, $password);

        if (!$result['ok']) {
            Session::flashValidation(['email' => (string) $result['error']], ['email' => $email]);

            return $this->redirect('/admin/login');
        }

        RateLimiter::clear('login', $this->request->ip());

        $intended = (string) Session::pull('_intended', '/admin');
        $target   = str_starts_with($intended, '/admin') ? $intended : '/admin';

        Session::flashSuccess('Signed in successfully.');

        return $this->redirect($target);
    }

    public function logout(): Response
    {
        if (!$this->request->isPost()) {
            throw new HttpException(405, 'Use POST to sign out.');
        }

        $this->assertCsrf();

        User::logout();
        Session::flashSuccess('You have been signed out.');

        return $this->redirect('/admin/login');
    }
}
