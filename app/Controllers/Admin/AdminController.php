<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

/**
 * Base class for every admin controller.
 *
 * Enforces authentication, adds noindex headers and switches the layout.
 */
abstract class AdminController extends Controller
{
    protected string $layout = 'layouts/admin';

    public function __construct(?\App\Core\Request $request = null)
    {
        parent::__construct($request);

        if (!User::check()) {
            Session::flash('_intended', $this->request->path());

            throw new \App\Core\Exceptions\HttpException(
                302,
                'Redirecting to sign in',
                ['Location' => $this->request->url('/admin/login')]
            );
        }
    }
}
